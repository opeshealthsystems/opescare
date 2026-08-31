"""
Resolve a git merge conflict in a locale JSON file by deep-merging the two
sides as JSON objects, rather than hand-editing conflict markers.

Every screen agent adds its own top-level i18n namespace additively, so a
"conflict" here is never a real disagreement — it's just git's line-based
merge choking on two independent JSON insertions at the same point. Deep
merging the parsed objects (recursively) is always correct for this
project's convention: each agent owns a distinct namespace and never edits
another's keys.

Usage: python merge_locales.py <path/to/conflicted.json>
Reads the conflicted file's two sides directly from the git index (stage 2
"ours", stage 3 "theirs"), deep-merges them, and overwrites the working file
with the resolved, validated JSON. Does NOT run `git add` — caller does that.
"""
import json
import subprocess
import sys


def git_show_stage(stage, path):
    result = subprocess.run(
        ["git", "show", f":{stage}:{path}"],
        capture_output=True, text=True, encoding="utf-8", check=True,
    )
    return json.loads(result.stdout)


def deep_merge(a, b):
    if isinstance(a, dict) and isinstance(b, dict):
        merged = dict(a)
        for k, v in b.items():
            merged[k] = deep_merge(merged[k], v) if k in merged else v
        return merged
    # Leaf conflict (same key, different scalar value) — keep "ours" (a) but
    # this should not happen given the additive-namespace convention; if it
    # does, it needs a human look, so surface it loudly.
    if a != b:
        print(f"WARNING: leaf conflict, keeping 'ours': {a!r} vs {b!r}", file=sys.stderr)
    return a


def main():
    if len(sys.argv) != 2:
        print("Usage: python merge_locales.py <path/to/conflicted.json>")
        sys.exit(1)
    path = sys.argv[1]
    ours = git_show_stage(2, path)
    theirs = git_show_stage(3, path)
    merged = deep_merge(ours, theirs)
    with open(path, "w", encoding="utf-8") as f:
        json.dump(merged, f, ensure_ascii=False, indent=2)
        f.write("\n")
    print(f"Resolved {path} ({len(json.dumps(merged))} bytes)")


if __name__ == "__main__":
    main()
