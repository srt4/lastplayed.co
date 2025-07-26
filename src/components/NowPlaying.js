import React from 'react';
import './NowPlaying.css';

const NowPlaying = ({ 
  song, 
  artist, 
  spotifyUrl, 
  stationName, 
  isStarred, 
  onToggleStar, 
  lastUpdated,
  isOffline 
}) => {
  const songKey = `${song}:::${artist}`;

  return (
    <div className="now-playing-card">
      <h4 className="station-title">Now Playing on {stationName}</h4>
      
      <div className="song-section">
        <h5 className="section-label">Song</h5>
        <h1 className="song-title">{song || 'Loading...'}</h1>
      </div>
      
      <div className="artist-section">
        <h5 className="section-label">Artist</h5>
        <h1 className="artist-title">{artist || 'Loading...'}</h1>
      </div>
      
      <div className="now-playing-actions">
        <div className="spotify-link">
          <a href={spotifyUrl} target="_blank" rel="noopener noreferrer">
            <img src="/thirdParty/spotify.svg" alt="Open in Spotify" />
          </a>
        </div>
        <button 
          className={`star-button ${isStarred ? 'starred' : ''}`}
          onClick={() => onToggleStar(songKey)}
        >
          ★
        </button>
      </div>
      
      <div className="update-indicator">
        Last updated: {lastUpdated ? lastUpdated.toLocaleTimeString() : 'Never'}
        {isOffline && ' (cached)'}
      </div>
    </div>
  );
};

export default NowPlaying;