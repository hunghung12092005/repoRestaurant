<x-mail::message>
{{-- Custom Header --}}
<x-slot:header>
<x-mail::header :url="config('app.url')">
    <div style="background-color: #a58a6f; padding: 15px; text-align: center;">
        <img src="https://i.postimg.cc/d3pNGXPN/7c6764b8-de90-474c-9b98-05019aef3193.png" alt="{{ config('app.name') }} Logo" style="max-width: 80px; filter: brightness(0) invert(1);">
    </div>
</x-mail::header>
</x-slot:header>

{{-- Email Body --}}
# Phản hồi về yêu cầu của bạn

**Kính gửi {{ $contact->name }},**

Chúng tôi xin chân thành cảm ơn bạn đã dành thời gian liên hệ với **{{ config('app.name') }}**.

### Lời nhắn của bạn
<x-mail::panel>
"{{ $contact->message }}"
</x-mail::panel>

### Phản hồi của chúng tôi
<div style="padding: 15px; border-left: 4px solid #a58a6f; background-color: #f7f5f3; margin-bottom: 20px;">
    <p style="margin: 0; font-size: 15px; line-height: 1.6;">
        {!! nl2br(e($replyMessage)) !!}
    </p>
</div>


Một lần nữa, cảm ơn bạn đã tin tưởng.

Trân trọng,<br>
Đội ngũ {{ config('app.name') }}

{{-- Custom Footer --}}
<x-slot:footer>
<x-mail::footer>
<div style="text-align: center; font-size: 12px; color: #777;">
    © {{ date('Y') }} {{ config('app.name') }}. Đã đăng ký bản quyền.<br>
    Địa chỉ: Đường Hồ Xuân Hương, Phường Quảng Cư, TP. Sầm Sơn, Thanh Hóa
</div>
</x-mail::footer>
</x-slot:footer>

</x-mail::message>