# Wave PR-4: Billing & Insurance Completion

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Complete 6 billing/insurance gaps — pre-authorization model, claims + remittance, co-pay calculation, Mobile Money (MTN MoMo/Orange Money), revenue cycle dashboard, and patient payment plans.

**Architecture:** All additions extend the existing Insurance module. New models for ClaimSubmission, RemittanceAdvice, PaymentPlan. Mobile Money implemented as a payment gateway service (strategy pattern). Revenue cycle is a read-only aggregation query service. No existing billing/insurance files are modified beyond adding to `$fillable`.

**Tech Stack:** Laravel 13, PHP 8.3, PostgreSQL, PHPUnit, HTTP client for MoMo APIs

---

## File Map

```
database/migrations/
  2026_05_26_400001_create_claim_submissions_table.php
  2026_05_26_400002_create_remittance_advices_table.php
  2026_05_26_400003_create_payment_plans_table.php
  2026_05_26_400004_create_payment_plan_installments_table.php
  2026_05_26_400005_add_copay_fields_to_billing_records.php
  2026_05_26_400006_create_mobile_money_transactions_table.php
app/Models/
  ClaimSubmission.php
  RemittanceAdvice.php
  PaymentPlan.php
  PaymentPlanInstallment.php
  MobileMoneyTransaction.php
app/Services/
  Billing/CopayCalculationService.php
  Billing/RevenueCycleService.php
  Payment/MobileMoneyService.php
  Payment/MtnMomoGateway.php
  Payment/OrangeMoneyGateway.php
tests/Feature/Billing/
  ClaimSubmissionTest.php
  CopayCalculationTest.php
  MobileMoneyTest.php
  RevenueCycleTest.php
  PaymentPlanTest.php
```

---

### Task 1: Claim Submissions + Remittance Advice

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Billing/ClaimSubmissionTest.php
namespace Tests\Feature\Billing;

use App\Models\Patient;
use App\Models\User;
use App\Models\Facility;
use App\Models\ClaimSubmission;
use App\Models\RemittanceAdvice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClaimSubmissionTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_claim_submission(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $claim = ClaimSubmission::create([
            'patient_id'    => $patient->id,
            'facility_id'   => $facility->id,
            'insurer_name'  => 'CNAMGS',
            'claim_number'  => 'CLM-2026-001',
            'service_date'  => '2026-06-01',
            'billed_amount' => 150000,
            'diagnosis_codes'=> ['E11.9', 'I10'],
            'status'        => 'submitted',
        ]);

        $this->assertEquals('submitted', $claim->status);
        $this->assertEquals(150000, $claim->billed_amount);
        $this->assertContains('E11.9', $claim->diagnosis_codes);
    }

    public function test_can_record_remittance(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $claim = ClaimSubmission::create([
            'patient_id'     => $patient->id,
            'facility_id'    => $facility->id,
            'insurer_name'   => 'CNAMGS',
            'claim_number'   => 'CLM-2026-002',
            'service_date'   => '2026-06-01',
            'billed_amount'  => 150000,
            'diagnosis_codes'=> ['E11.9'],
            'status'         => 'submitted',
        ]);

        $remittance = RemittanceAdvice::create([
            'claim_submission_id' => $claim->id,
            'paid_amount'         => 120000,
            'adjustment_amount'   => 30000,
            'adjustment_reason'   => 'Deductible applied',
            'paid_on'             => '2026-07-01',
            'payment_reference'   => 'CNAMGS-PAY-001',
        ]);

        $claim->update(['status' => 'paid', 'paid_amount' => 120000]);

        $this->assertEquals(120000, $remittance->paid_amount);
        $this->assertEquals('paid', $claim->fresh()->status);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Billing/ClaimSubmissionTest.php
```

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_400001_create_claim_submissions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('claim_submissions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->string('insurer_name');
            $table->string('claim_number')->unique();
            $table->date('service_date');
            $table->unsignedBigInteger('billed_amount'); // in XAF centimes
            $table->unsignedBigInteger('paid_amount')->nullable();
            $table->json('diagnosis_codes');
            $table->json('procedure_codes')->nullable();
            $table->enum('status', ['draft','submitted','under_review','paid','denied','appealed'])->default('draft');
            $table->timestamp('submitted_at')->nullable();
            $table->text('denial_reason')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('claim_submissions'); }
};
```

```php
<?php
// database/migrations/2026_05_26_400002_create_remittance_advices_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('remittance_advices', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('claim_submission_id')
                ->constrained('claim_submissions')->cascadeOnDelete();
            $table->unsignedBigInteger('paid_amount');
            $table->unsignedBigInteger('adjustment_amount')->default(0);
            $table->string('adjustment_reason')->nullable();
            $table->date('paid_on');
            $table->string('payment_reference')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('remittance_advices'); }
};
```

- [ ] **Step 4: Create models**

```php
<?php
// app/Models/ClaimSubmission.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class ClaimSubmission extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','facility_id','insurer_name','claim_number',
        'service_date','billed_amount','paid_amount','diagnosis_codes',
        'procedure_codes','status','submitted_at','denial_reason',
    ];

    protected $casts = [
        'service_date'    => 'date',
        'submitted_at'    => 'datetime',
        'diagnosis_codes' => 'array',
        'procedure_codes' => 'array',
    ];

    public function patient()   { return $this->belongsTo(Patient::class); }
    public function facility()  { return $this->belongsTo(Facility::class); }
    public function remittances(){ return $this->hasMany(RemittanceAdvice::class); }
}
```

```php
<?php
// app/Models/RemittanceAdvice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class RemittanceAdvice extends Model
{
    use HasUuids;

    protected $fillable = [
        'claim_submission_id','paid_amount','adjustment_amount',
        'adjustment_reason','paid_on','payment_reference',
    ];

    protected $casts = ['paid_on' => 'date'];

    public function claim() { return $this->belongsTo(ClaimSubmission::class, 'claim_submission_id'); }
}
```

- [ ] **Step 5: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Billing/ClaimSubmissionTest.php
```

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_400001_* database/migrations/2026_05_26_400002_* \
  app/Models/ClaimSubmission.php app/Models/RemittanceAdvice.php \
  tests/Feature/Billing/ClaimSubmissionTest.php
git commit -m "feat(billing): claim submissions and remittance advice"
```

---

### Task 2: Co-pay Calculation at Point of Care

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Billing/CopayCalculationTest.php
namespace Tests\Feature\Billing;

use App\Services\Billing\CopayCalculationService;
use Tests\TestCase;

class CopayCalculationTest extends TestCase
{
    public function test_fixed_copay_calculated_correctly(): void
    {
        $service = new CopayCalculationService();
        $result  = $service->calculate(
            billedAmount:    150000,
            insurancePct:    80,
            copayType:       'fixed',
            copayValue:      5000,
        );

        $this->assertEquals(5000, $result['patient_copay']);
        $this->assertEquals(145000, $result['insurance_portion']);
    }

    public function test_percentage_copay_calculated_correctly(): void
    {
        $service = new CopayCalculationService();
        $result  = $service->calculate(
            billedAmount:    200000,
            insurancePct:    80,
            copayType:       'percentage',
            copayValue:      20,
        );

        $this->assertEquals(40000, $result['patient_copay']);
        $this->assertEquals(160000, $result['insurance_portion']);
    }

    public function test_deductible_reduces_insurance_payout(): void
    {
        $service = new CopayCalculationService();
        $result  = $service->calculate(
            billedAmount:    100000,
            insurancePct:    80,
            copayType:       'fixed',
            copayValue:      0,
            deductible:      20000,
        );

        // Patient pays deductible first, then insurer pays 80% of remainder
        // Remainder = 80000; Insurance = 64000; Patient total = 20000 + 16000 = 36000
        $this->assertEquals(36000, $result['patient_copay']);
        $this->assertEquals(64000, $result['insurance_portion']);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Billing/CopayCalculationTest.php
```

- [ ] **Step 3: Create CopayCalculationService**

```php
<?php
// app/Services/Billing/CopayCalculationService.php
namespace App\Services\Billing;

class CopayCalculationService
{
    /**
     * @param  int    $billedAmount     Total billed in XAF
     * @param  int    $insurancePct     Insurance coverage percentage (0-100)
     * @param  string $copayType        'fixed' or 'percentage'
     * @param  int    $copayValue       Fixed XAF or percentage integer
     * @param  int    $deductible       Annual deductible remaining in XAF
     * @return array  patient_copay, insurance_portion, total_billed, breakdown
     */
    public function calculate(
        int    $billedAmount,
        int    $insurancePct,
        string $copayType,
        int    $copayValue,
        int    $deductible = 0,
    ): array {
        $patientPays = 0;

        // 1. Apply deductible first
        $deductibleApplied = min($deductible, $billedAmount);
        $patientPays       += $deductibleApplied;
        $remainingBilled   = $billedAmount - $deductibleApplied;

        // 2. Apply copay on remainder
        $copayAmount = match ($copayType) {
            'fixed'      => $copayValue,
            'percentage' => (int) round($remainingBilled * $copayValue / 100),
            default      => 0,
        };

        // 3. Insurance covers its pct of remainder minus copay
        $insurancePortion = (int) round($remainingBilled * $insurancePct / 100) - $copayAmount;
        $insurancePortion = max(0, $insurancePortion);

        $patientPays += ($remainingBilled - $insurancePortion);

        return [
            'total_billed'      => $billedAmount,
            'deductible_applied'=> $deductibleApplied,
            'patient_copay'     => $patientPays,
            'insurance_portion' => $insurancePortion,
        ];
    }
}
```

- [ ] **Step 4: Run tests**

```bash
php artisan test tests/Feature/Billing/CopayCalculationTest.php
```

- [ ] **Step 5: Commit**

```bash
git add app/Services/Billing/CopayCalculationService.php \
  tests/Feature/Billing/CopayCalculationTest.php
git commit -m "feat(billing): co-pay and co-insurance calculation at point of care"
```

---

### Task 3: Mobile Money Payments (MTN MoMo + Orange Money)

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Billing/MobileMoneyTest.php
namespace Tests\Feature\Billing;

use App\Models\Patient;
use App\Models\Facility;
use App\Models\MobileMoneyTransaction;
use App\Services\Payment\MobileMoneyService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class MobileMoneyTest extends TestCase
{
    use RefreshDatabase;

    public function test_mtn_momo_payment_initiation(): void
    {
        Http::fake([
            '*/v1_0/requesttopay' => Http::response(['status' => 'PENDING'], 202),
            '*/apiuser'           => Http::response([], 201),
            '*/apikey'            => Http::response(['apiKey' => 'test-key'], 201),
            '*/token'             => Http::response(['access_token' => 'mock-token', 'token_type' => 'Bearer', 'expires_in' => 3600], 200),
        ]);

        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $service = new MobileMoneyService();
        $txn     = $service->initiatePayment(
            provider:    'mtn_momo',
            patientId:   $patient->id,
            facilityId:  $facility->id,
            amountXaf:   25000,
            phoneNumber: '+237670000001',
            reference:   'BILL-2026-001',
            description: 'Consultation fee',
        );

        $this->assertInstanceOf(MobileMoneyTransaction::class, $txn);
        $this->assertEquals('pending', $txn->status);
        $this->assertEquals('mtn_momo', $txn->provider);
    }

    public function test_mobile_money_transaction_stored(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $txn = MobileMoneyTransaction::create([
            'patient_id'    => $patient->id,
            'facility_id'   => $facility->id,
            'provider'      => 'orange_money',
            'amount_xaf'    => 15000,
            'phone_number'  => '+237695000001',
            'reference'     => 'BILL-2026-002',
            'status'        => 'pending',
            'provider_ref'  => 'OM-TXN-12345',
        ]);

        $this->assertEquals('orange_money', $txn->provider);
        $this->assertEquals(15000, $txn->amount_xaf);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Billing/MobileMoneyTest.php
```

- [ ] **Step 3: Create migration**

```php
<?php
// database/migrations/2026_05_26_400006_create_mobile_money_transactions_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('mobile_money_transactions', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->enum('provider', ['mtn_momo', 'orange_money']);
            $table->unsignedBigInteger('amount_xaf');
            $table->string('phone_number', 25);
            $table->string('reference')->unique();
            $table->string('description')->nullable();
            $table->enum('status', ['pending','completed','failed','cancelled'])->default('pending');
            $table->string('provider_ref')->nullable();  // provider's transaction ID
            $table->json('provider_response')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('mobile_money_transactions'); }
};
```

- [ ] **Step 4: Create MobileMoneyTransaction model**

```php
<?php
// app/Models/MobileMoneyTransaction.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class MobileMoneyTransaction extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','facility_id','provider','amount_xaf',
        'phone_number','reference','description','status',
        'provider_ref','provider_response','completed_at',
    ];

    protected $casts = [
        'provider_response' => 'array',
        'completed_at'      => 'datetime',
    ];

    public function patient()  { return $this->belongsTo(Patient::class); }
    public function facility() { return $this->belongsTo(Facility::class); }
}
```

- [ ] **Step 5: Create MobileMoneyService + MtnMomoGateway**

```php
<?php
// app/Services/Payment/MobileMoneyService.php
namespace App\Services\Payment;

use App\Models\MobileMoneyTransaction;
use Illuminate\Support\Str;

class MobileMoneyService
{
    public function initiatePayment(
        string $provider,
        string $patientId,
        string $facilityId,
        int    $amountXaf,
        string $phoneNumber,
        string $reference,
        string $description = '',
    ): MobileMoneyTransaction {
        $txn = MobileMoneyTransaction::create([
            'patient_id'   => $patientId,
            'facility_id'  => $facilityId,
            'provider'     => $provider,
            'amount_xaf'   => $amountXaf,
            'phone_number' => $phoneNumber,
            'reference'    => $reference,
            'description'  => $description,
            'status'       => 'pending',
        ]);

        $gateway = match ($provider) {
            'mtn_momo'     => new MtnMomoGateway(),
            'orange_money' => new OrangeMoneyGateway(),
            default        => throw new \InvalidArgumentException("Unknown provider: {$provider}"),
        };

        try {
            $result = $gateway->requestPayment(
                amountXaf:   $amountXaf,
                phoneNumber: $phoneNumber,
                reference:   $reference,
                description: $description,
            );

            $txn->update([
                'provider_ref'      => $result['provider_ref'] ?? null,
                'provider_response' => $result,
                'status'            => $result['success'] ? 'pending' : 'failed',
            ]);
        } catch (\Exception $e) {
            $txn->update(['status' => 'failed', 'provider_response' => ['error' => $e->getMessage()]]);
        }

        return $txn->fresh();
    }
}
```

```php
<?php
// app/Services/Payment/MtnMomoGateway.php
namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class MtnMomoGateway
{
    private string $baseUrl;
    private string $subscriptionKey;
    private string $environment;

    public function __construct()
    {
        $this->baseUrl         = config('services.mtn_momo.base_url', 'https://sandbox.momodeveloper.mtn.com');
        $this->subscriptionKey = config('services.mtn_momo.subscription_key', '');
        $this->environment     = config('services.mtn_momo.environment', 'sandbox');
    }

    public function requestPayment(int $amountXaf, string $phoneNumber, string $reference, string $description): array
    {
        $token       = $this->getAccessToken();
        $referenceId = (string) Str::uuid();

        $response = Http::withToken($token)
            ->withHeaders([
                'X-Reference-Id'    => $referenceId,
                'X-Target-Environment' => $this->environment,
                'Ocp-Apim-Subscription-Key' => $this->subscriptionKey,
            ])
            ->post("{$this->baseUrl}/collection/v1_0/requesttopay", [
                'amount'       => (string) $amountXaf,
                'currency'     => 'XAF',
                'externalId'   => $reference,
                'payer'        => ['partyIdType' => 'MSISDN', 'partyId' => ltrim($phoneNumber, '+')],
                'payerMessage' => $description,
                'payeeNote'    => $description,
            ]);

        return [
            'success'      => $response->status() === 202,
            'provider_ref' => $referenceId,
            'http_status'  => $response->status(),
        ];
    }

    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth(
            config('services.mtn_momo.api_user'),
            config('services.mtn_momo.api_key')
        )->withHeaders(['Ocp-Apim-Subscription-Key' => $this->subscriptionKey])
          ->post("{$this->baseUrl}/collection/token/");

        return $response->json('access_token', '');
    }
}
```

```php
<?php
// app/Services/Payment/OrangeMoneyGateway.php
namespace App\Services\Payment;

use Illuminate\Support\Facades\Http;

class OrangeMoneyGateway
{
    private string $baseUrl;
    private string $merchantKey;
    private string $username;
    private string $password;

    public function __construct()
    {
        $this->baseUrl     = config('services.orange_money.base_url', 'https://api.orange.com/orange-money-webpay/cm/v1');
        $this->merchantKey = config('services.orange_money.merchant_key', '');
        $this->username    = config('services.orange_money.username', '');
        $this->password    = config('services.orange_money.password', '');
    }

    public function requestPayment(int $amountXaf, string $phoneNumber, string $reference, string $description): array
    {
        $token    = $this->getAccessToken();

        $response = Http::withToken($token)
            ->post("{$this->baseUrl}/webpayment", [
                'merchant_key'   => $this->merchantKey,
                'currency'       => 'XAF',
                'order_id'       => $reference,
                'amount'         => $amountXaf,
                'return_url'     => config('app.url') . '/api/payments/orange-money/callback',
                'cancel_url'     => config('app.url') . '/api/payments/orange-money/cancel',
                'notif_url'      => config('app.url') . '/api/payments/orange-money/notify',
                'lang'           => 'fr',
                'reference'      => $reference,
            ]);

        return [
            'success'        => $response->successful(),
            'provider_ref'   => $response->json('pay_token'),
            'payment_url'    => $response->json('payment_url'),
            'http_status'    => $response->status(),
        ];
    }

    private function getAccessToken(): string
    {
        $response = Http::withBasicAuth($this->username, $this->password)
            ->asForm()
            ->post('https://api.orange.com/oauth/v3/token', ['grant_type' => 'client_credentials']);

        return $response->json('access_token', '');
    }
}
```

- [ ] **Step 6: Add config entries**

In `config/services.php`, add:
```php
'mtn_momo' => [
    'base_url'         => env('MTN_MOMO_BASE_URL', 'https://sandbox.momodeveloper.mtn.com'),
    'subscription_key' => env('MTN_MOMO_SUBSCRIPTION_KEY'),
    'api_user'         => env('MTN_MOMO_API_USER'),
    'api_key'          => env('MTN_MOMO_API_KEY'),
    'environment'      => env('MTN_MOMO_ENVIRONMENT', 'sandbox'),
],
'orange_money' => [
    'base_url'     => env('ORANGE_MONEY_BASE_URL', 'https://api.orange.com/orange-money-webpay/cm/v1'),
    'merchant_key' => env('ORANGE_MONEY_MERCHANT_KEY'),
    'username'     => env('ORANGE_MONEY_USERNAME'),
    'password'     => env('ORANGE_MONEY_PASSWORD'),
],
```

In `.env.example`, add:
```
MTN_MOMO_BASE_URL=https://sandbox.momodeveloper.mtn.com
MTN_MOMO_SUBSCRIPTION_KEY=
MTN_MOMO_API_USER=
MTN_MOMO_API_KEY=
MTN_MOMO_ENVIRONMENT=sandbox

ORANGE_MONEY_BASE_URL=https://api.orange.com/orange-money-webpay/cm/v1
ORANGE_MONEY_MERCHANT_KEY=
ORANGE_MONEY_USERNAME=
ORANGE_MONEY_PASSWORD=
```

- [ ] **Step 7: Run tests**

```bash
php artisan migrate && php artisan test tests/Feature/Billing/MobileMoneyTest.php
```

- [ ] **Step 8: Commit**

```bash
git add database/migrations/2026_05_26_400006_* app/Models/MobileMoneyTransaction.php \
  app/Services/Payment/ config/services.php .env.example \
  tests/Feature/Billing/MobileMoneyTest.php
git commit -m "feat(billing): MTN MoMo and Orange Money mobile payment gateways"
```

---

### Task 4: Patient Payment Plans

- [ ] **Step 1: Write failing tests**

```php
<?php
// tests/Feature/Billing/PaymentPlanTest.php
namespace Tests\Feature\Billing;

use App\Models\Patient;
use App\Models\Facility;
use App\Models\PaymentPlan;
use App\Models\PaymentPlanInstallment;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PaymentPlanTest extends TestCase
{
    use RefreshDatabase;

    public function test_can_create_payment_plan_with_installments(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $plan = PaymentPlan::create([
            'patient_id'      => $patient->id,
            'facility_id'     => $facility->id,
            'total_amount'    => 300000,
            'installments'    => 3,
            'frequency'       => 'monthly',
            'start_date'      => '2026-07-01',
            'status'          => 'active',
        ]);

        // Auto-generate 3 monthly installments
        for ($i = 0; $i < 3; $i++) {
            PaymentPlanInstallment::create([
                'payment_plan_id' => $plan->id,
                'due_date'        => now()->addMonths($i)->toDateString(),
                'amount'          => 100000,
                'status'          => 'pending',
            ]);
        }

        $this->assertCount(3, $plan->installments);
        $this->assertEquals(300000, $plan->total_amount);
    }

    public function test_installment_can_be_marked_paid(): void
    {
        $patient  = Patient::factory()->create();
        $facility = Facility::factory()->create();

        $plan = PaymentPlan::create([
            'patient_id'   => $patient->id,
            'facility_id'  => $facility->id,
            'total_amount' => 100000,
            'installments' => 1,
            'frequency'    => 'monthly',
            'start_date'   => '2026-07-01',
            'status'       => 'active',
        ]);

        $installment = PaymentPlanInstallment::create([
            'payment_plan_id' => $plan->id,
            'due_date'        => '2026-07-01',
            'amount'          => 100000,
            'status'          => 'pending',
        ]);

        $installment->update(['status' => 'paid', 'paid_at' => now()]);
        $this->assertEquals('paid', $installment->fresh()->status);
    }
}
```

- [ ] **Step 2: Run to confirm fail**

```bash
php artisan test tests/Feature/Billing/PaymentPlanTest.php
```

- [ ] **Step 3: Create migrations**

```php
<?php
// database/migrations/2026_05_26_400003_create_payment_plans_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_plans', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('patient_id')->constrained('patients')->cascadeOnDelete();
            $table->foreignUuid('facility_id')->constrained('facilities')->cascadeOnDelete();
            $table->unsignedBigInteger('total_amount');
            $table->unsignedTinyInteger('installments');
            $table->enum('frequency', ['weekly','biweekly','monthly'])->default('monthly');
            $table->date('start_date');
            $table->enum('status', ['active','completed','defaulted','cancelled'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('payment_plans'); }
};
```

```php
<?php
// database/migrations/2026_05_26_400004_create_payment_plan_installments_table.php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('payment_plan_installments', function (Blueprint $table) {
            $table->uuid('id')->primary()->default(DB::raw('gen_random_uuid()'));
            $table->foreignUuid('payment_plan_id')
                ->constrained('payment_plans')->cascadeOnDelete();
            $table->date('due_date');
            $table->unsignedBigInteger('amount');
            $table->enum('status', ['pending','paid','overdue','waived'])->default('pending');
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_method')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void { Schema::dropIfExists('payment_plan_installments'); }
};
```

- [ ] **Step 4: Create models**

```php
<?php
// app/Models/PaymentPlan.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentPlan extends Model
{
    use HasUuids;

    protected $fillable = [
        'patient_id','facility_id','total_amount','installments',
        'frequency','start_date','status','notes',
    ];

    protected $casts = ['start_date' => 'date'];

    public function patient()      { return $this->belongsTo(Patient::class); }
    public function facility()     { return $this->belongsTo(Facility::class); }
    public function installments() { return $this->hasMany(PaymentPlanInstallment::class); }
}
```

```php
<?php
// app/Models/PaymentPlanInstallment.php
namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

class PaymentPlanInstallment extends Model
{
    use HasUuids;

    protected $fillable = [
        'payment_plan_id','due_date','amount','status','paid_at','payment_method',
    ];

    protected $casts = [
        'due_date' => 'date',
        'paid_at'  => 'datetime',
    ];

    public function plan() { return $this->belongsTo(PaymentPlan::class, 'payment_plan_id'); }
}
```

- [ ] **Step 5: Run tests + full suite**

```bash
php artisan migrate && php artisan test tests/Feature/Billing/PaymentPlanTest.php && php artisan test
```
Expected: All green.

- [ ] **Step 6: Commit**

```bash
git add database/migrations/2026_05_26_400003_* database/migrations/2026_05_26_400004_* \
  app/Models/PaymentPlan.php app/Models/PaymentPlanInstallment.php \
  tests/Feature/Billing/PaymentPlanTest.php
git commit -m "feat(billing): patient payment plans with installment tracking"
```
