{{ 'Welcome to Admin Blog page, Content will be soon!' }}
<?php $id = 6; ?>

<form method="POST" action="/admin/blog/store" enctype="multipart/form-data">
    {{ csrf_field() }}
    <input type="file" name="g">
    <input type="submit">
</form>
{{ $errors->first('blogImage') ?? '' }}
