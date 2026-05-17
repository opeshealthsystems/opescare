<?php

$models = [
    'Partner',
    'PartnerFacility',
    'PartnerProfessional',
    'PartnerContact',
    'PartnerDocument',
    'PartnerAgreement',
    'PartnerContributionPermission',
    'PartnerAccessPermission',
    'PartnerIntegration',
    'PartnerContribution',
    'PartnerQualityScore',
    'PartnerRiskScore',
    'PartnerGovernanceCase'
];

$dir = __DIR__ . '/../app/Modules/Partners/Models';

foreach ($models as $model) {
    $content = "<?php\n\nnamespace App\Modules\Partners\Models;\n\nuse Illuminate\Database\Eloquent\Factories\HasFactory;\nuse Illuminate\Database\Eloquent\Model;\n\nclass {$model} extends Model\n{\n    use HasFactory;\n\n    protected \$guarded = ['id'];\n}\n";
    file_put_contents("{$dir}/{$model}.php", $content);
}

echo "Created 13 models.\n";
