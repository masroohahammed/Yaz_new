<?php

namespace App\Services;

/**
 * Default Contract Setup body for parking agreements — mirrors legacy print articles 1–4.
 */
class ParkingContractTemplateDefaults
{
    public static function contentEn(): string
    {
        return <<<'HTML'
<p><strong>Article One: Term and Rent</strong></p>
<p>Term: {{duration_en}}, from {{start_date_en}} to {{end_date_en}}.<br>
Monthly rent QAR {{rent_amount_fmt}} ({{rent_words_en}}), payable in advance via {{cheque_count}} cheques to {{collector_company}} {{collector_account}}.</p>
<p><strong>Article Two: Lessor Obligations</strong></p>
<p>Hand over Parking No. ({{parking_unit_no}}) for vehicle Reg. {{plate_number}}.</p>
<p><strong>Article Three: Lessee Obligations</strong></p>
<ol>
<li>Pay rent on due dates.</li>
<li>Not sublease the parking space.</li>
<li>No mechanical work; keep the space clean.</li>
<li>Use only for the specified vehicle unless approved in writing.</li>
<li>Return the space in the same condition.</li>
</ol>
<p><strong>Article Four: General Terms</strong></p>
<p>Late payment or breach terminates the Agreement; Lessor may remove the vehicle without legal proceedings. Governed by Qatari law; Qatari courts have jurisdiction. Executed in two original copies.</p>
HTML;
    }

    public static function contentAr(): string
    {
        return <<<'HTML'
<p><strong>البند الأول: المدة والأجرة</strong></p>
<p>مدة {{duration_ar}} من {{start_date_ar}} إلى {{end_date_ar}}.<br>
الأجرة الشهرية {{rent_amount_fmt}} ريال قطري، {{cheque_count}} شيكاً لحساب {{collector_company}} {{collector_account}}.</p>
<p><strong>البند الثاني: التزامات المالك</strong></p>
<p>تسليم الموقف ({{parking_unit_no}}) للمركبة ({{plate_number}}).</p>
<p><strong>البند الثالث: التزامات المستأجر</strong></p>
<ol>
<li>تسديد الأجرة في موعدها.</li>
<li>عدم تأجير الموقف من الباطن.</li>
<li>عدم أعمال ميكانيكية والمحافظة على النظافة.</li>
<li>تخصيص الموقف للمركبة المذكورة فقط.</li>
<li>إعادة الموقف بالحالة الأصلية.</li>
</ol>
<p><strong>البند الرابع: الشروط العامة</strong></p>
<p>التأخر أو المخالفة يلغي العقد تلقائياً ويحق للمالك إخلاء المركبة. يخضع لقوانين قطر وتختص المحاكم القطرية. أُبرم من نسختين أصليتين.</p>
HTML;
    }

    /** @return array{content_en: string, content_ar: string, terms_en: string, terms_ar: string} */
    public static function templateRow(): array
    {
        return [
            'content_en' => self::contentEn(),
            'content_ar' => self::contentAr(),
            'terms_en'   => '',
            'terms_ar'   => '',
        ];
    }
}
