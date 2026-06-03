<!doctype html>
<html>
<head><meta charset="utf-8"><title>New consultation enquiry</title></head>
<body style="font-family: Arial, sans-serif; color:#0f2b4d; line-height:1.6;">
    <h2 style="margin-bottom:8px;">New consultation enquiry</h2>
    <p>You have received a new enquiry for <strong>{{ $service?->name ?? 'a matching consultant service' }}</strong>.</p>
    <table cellpadding="8" cellspacing="0" style="border-collapse:collapse; width:100%; max-width:680px;">
        <tr><td style="border:1px solid #e5edf5;"><strong>Category</strong></td><td style="border:1px solid #e5edf5;">{{ isset($category) ? $category->name : ($service?->categoryModel?->name ?? '—') }}</td></tr>
        <tr><td style="border:1px solid #e5edf5;"><strong>Sub category</strong></td><td style="border:1px solid #e5edf5;">{{ isset($subcategory) ? $subcategory->name : ($service?->subcategoryModel?->name ?? '—') }}</td></tr>
        <tr><td style="border:1px solid #e5edf5;"><strong>Client name</strong></td><td style="border:1px solid #e5edf5;">{{ $inquiry->client_name }}</td></tr>
        <tr><td style="border:1px solid #e5edf5;"><strong>Phone</strong></td><td style="border:1px solid #e5edf5;">{{ $inquiry->phone_number }}</td></tr>
        <tr><td style="border:1px solid #e5edf5;"><strong>Email</strong></td><td style="border:1px solid #e5edf5;">{{ $inquiry->email }}</td></tr>
        <tr><td style="border:1px solid #e5edf5;"><strong>Occupation</strong></td><td style="border:1px solid #e5edf5;">{{ $inquiry->occupation ?: '—' }}</td></tr>
        <tr><td style="border:1px solid #e5edf5;"><strong>Date of birth</strong></td><td style="border:1px solid #e5edf5;">{{ $inquiry->date_of_birth?->format('d M Y') ?? '—' }}</td></tr>
        <tr><td style="border:1px solid #e5edf5;"><strong>Question</strong></td><td style="border:1px solid #e5edf5;">{{ $inquiry->question }}</td></tr>
    </table>
    <p style="margin-top:18px;">Please log in to your consultant portal to view and respond to this enquiry.</p>
</body>
</html>
