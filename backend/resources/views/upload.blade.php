<!-- resources/views/upload.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <title>Tải Lên Ảnh</title>
</head>
<body>

@if ($message = Session::get('success'))
    <div>{{ $message }}</div>
@endif

<form action="/upload" method="POST" enctype="multipart/form-data">
    @csrf
    <input type="file" name="image" required multiple>
    <input type="text" name="item_id" value="" placeholder="id"> <!-- ID sản phẩm nếu cần -->
    <button type="submit">Tải Lên</button>
</form>

</body>
</html>