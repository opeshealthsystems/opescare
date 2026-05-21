@extends('layouts.docs')
@section('title', 'Interactive Playground')

@section('head')
<style>
  /* Override content padding for full-height Redoc */
  .docs-content {
    max-width: 100%;
    padding: 0;
    margin-left: var(--docs-sidebar-w);
  }
  .redoc-wrap {
    height: calc(100vh - 56px);
    overflow: auto;
  }
  @media (max-width: 768px) {
    .docs-content { margin-left: 0; }
  }
</style>
@endsection

@section('content')
<div class="redoc-wrap">
    <redoc
        spec-url='{{ asset("openapi.yaml") }}'
        hide-download-btn="false"
        expand-responses="200,201"
        required-props-first="true"
        sort-props-alphabetically="false"
        theme='{
            "colors": {
                "primary": { "main": "#4F46E5" },
                "success": { "main": "#22C55E" },
                "warning": { "main": "#F59E0B" },
                "error": { "main": "#EF4444" }
            },
            "typography": {
                "fontFamily": "Inter, -apple-system, BlinkMacSystemFont, sans-serif",
                "headings": { "fontWeight": "700" },
                "code": {
                    "fontFamily": "Fira Code, Consolas, monospace",
                    "fontSize": "13px"
                }
            },
            "sidebar": {
                "backgroundColor": "#1E293B",
                "textColor": "#E2E8F0"
            },
            "logo": {
                "gutter": "20px"
            }
        }'
    ></redoc>

    <script src="https://cdn.jsdelivr.net/npm/redoc@2.1.3/bundles/redoc.standalone.js"></script>
</div>
@endsection
