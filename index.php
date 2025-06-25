<?php

require_once('model/RadioStation.php');
require_once('callSigns/base/SpinitronStation.php');

$callSign = $_GET['call_sign'];

if ((@include "callSigns/" . strtolower($callSign) . ".php") === false) {
   exit ("Unknown station");
}

$stationClass = "\\callSigns\\" . strtoupper($callSign);
$station = new $stationClass();
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Now Playing on <?= strtoupper($callSign) ?></title>

  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>

  <style>
    body {
      background-color: #f4f4f4;
      color: #222;
      font-family: 'Segoe UI', sans-serif;
      padding: 1rem;
      margin: 0;
    }

    @media (prefers-color-scheme: dark) {
      body {
        background-color: #121212;
        color: #e0e0e0;
      }

      .card {
        background-color: #1f1f1f;
        box-shadow: 0 0 12px rgba(255, 255, 255, 0.05);
      }
    }

    .now-playing-card {
      border-radius: 20px;
      padding: 2rem;
      box-shadow: 0 6px 18px rgba(0, 0, 0, 0.1);
      background-color: white;
      margin-top: 5vh;
      margin-bottom: 5vh;
    }

    @media (prefers-color-scheme: dark) {
      .now-playing-card {
        background-color: #1f1f1f;
      }
    }

    .spotify-link img {
      transition: transform 0.2s ease, box-shadow 0.2s ease;
      border-radius: 12px;
    }

    .spotify-link img:hover {
      transform: scale(1.1);
      box-shadow: 0 0 15px rgba(30, 215, 96, 0.6);
    }

    .song-title, .artist-title {
      font-weight: 700;
      font-size: clamp(2rem, 5vw + 1rem, 3.5rem);
      line-height: 1.2;
      word-break: break-word;
    }

    @media (max-width: 576px) {
      .song-title, .artist-title {
        font-size: 2.4rem;
      }
    }

    .song-title {
      color: #1DB954;
      text-shadow: 0 0 5px rgba(30, 215, 96, 0.3);
    }

    .artist-title {
      color: #bbb;
      font-style: italic;
    }
  </style>
</head>
<body>

<div class="container d-flex justify-content-center">
  <div class="now-playing-card text-center w-100" style="max-width: 700px;">
    <h4 class="fw-bold mb-4">Now Playing on <?= strtoupper($callSign) ?></h4>

    <div class="mb-4">
      <h5 class="text-uppercase text-secondary">Song</h5>
      <h1 class="song-title mb-2"><?= $station->getCurrentSong() ?></h1>
    </div>

    <div class="mb-5">
      <h5 class="text-uppercase text-secondary">Artist</h5>
      <h1 class="artist-title"><?= $station->getCurrentArtist() ?></h1>
    </div>

    <div class="spotify-link">
      <a class="d-inline-block" target="_blank"
         href="https://play.spotify.com/search/<?= urlencode($station->getCurrentArtist() . ' ' . $station->getCurrentSong()) ?>">
        <img src="thirdParty/spotify.png" width="75" alt="Open in Spotify">
      </a>
    </div>
  </div>
</div>

</body>
</html>