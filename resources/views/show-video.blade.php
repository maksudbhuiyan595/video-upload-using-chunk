<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Document</title>
</head>`
<style>
    video {
        max-width: 100%;
        height: auto;
    }
</style>
<body>
    <h2>Play Video</h2>
    <video controls>
        <source src="{{ asset('storage/videos/' . $video->file_name) }}" type="video/mp4">
        Your browser does not support the video tag.
    </video>

</body>
</html>
