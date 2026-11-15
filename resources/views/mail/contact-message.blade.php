<!DOCTYPE html>
<html lang="ar" dir="rtl">
<body style="font-family:Tahoma,Arial,sans-serif;background:#FFF7FB;padding:24px;color:#1C1552">
    <div style="max-width:560px;margin:auto;background:#fff;border-radius:14px;padding:24px;border:1px solid #f3d6e4">
        <h2 style="margin-top:0;color:#E51B6E">رسالة جديدة من نموذج التواصل</h2>
        <p><b>الاسم:</b> {{ $contactMessage->name }}</p>
        <p><b>الجوال:</b> {{ $contactMessage->phone }}</p>
        <p><b>البريد:</b> {{ $contactMessage->email }}</p>
        <p><b>نوع الطلب:</b> {{ $contactMessage->subjectLabel() }}</p>
        <p><b>الرسالة:</b></p>
        <div style="white-space:pre-line;background:#FFF7FB;padding:12px;border-radius:10px">{{ $contactMessage->message }}</div>
        <p style="margin-top:20px"><a href="{{ route('admin.messages.show', $contactMessage) }}" style="color:#E51B6E">فتح الرسالة في لوحة التحكم</a></p>
    </div>
</body>
</html>
