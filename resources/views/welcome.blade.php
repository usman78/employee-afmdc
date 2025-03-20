<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta content="width=device-width, initial-scale=1.0" name="viewport">
  <title>Employee - AFMDC</title>

  <!-- Favicons -->

 
</head>

<body class="index-page">

    @foreach ($inventory as $i)
    {{ $i->inventory->item_desc }}
    @endforeach


</body>

</html>




