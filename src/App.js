import React, { useState, useEffect } from 'react';
import NowPlaying from './components/NowPlaying';
import History from './components/History';
import { useNowPlaying } from './hooks/useNowPlaying';
import './App.css';

const STATION_LIST = [
  { callSign: 'KEXP', name: 'KEXP - Seattle' },
  { callSign: 'KXLY', name: 'KXLY - Spokane' },
  { callSign: 'KBCS', name: 'KBCS - Bellevue' },
  { callSign: 'KBFG', name: 'KBFG - Seattle' },
  { callSign: 'KMGP', name: 'KMGP - Seattle' },
  { callSign: 'KNDD', name: 'KNDD - Seattle' },
  { callSign: 'KPNW', name: 'KPNW - Seattle' },
  { callSign: 'KUTX', name: 'KUTX - Austin' },
  { callSign: 'KXSU', name: 'KXSU - Seattle' },
  { callSign: 'KZOK', name: 'KZOK - Seattle' },
  { callSign: 'CKKQ', name: 'CKKQ - Port Alberni' }
];

const StationList = () => (
  <div className="station-list-container">
    <h3>Choose a Radio Station:</h3>
    <div className="station-list">
      {STATION_LIST.map(station => (
        <a 
          key={station.callSign}
          href={`https://${station.callSign.toLowerCase()}.currently.rocks`}
          className="station-link"
        >
          {station.name}
        </a>
      ))}
    </div>
  </div>
);

const ErrorDisplay = ({ message }) => (
  <div className="error-container">
    <h1 className="error-title">Error</h1>
    <p className="error-message">{message}</p>
    <a href="/" className="error-button">Try Again</a>
  </div>
);

const OfflineIndicator = ({ isVisible }) => (
  <div className={`offline-indicator ${isVisible ? 'visible' : ''}`}>
    You're offline. Showing last known songs.
  </div>
);

function App() {
  const [callSign, setCallSign] = useState('');

  useEffect(() => {
    // Get call sign from subdomain
    const hostname = window.location.hostname;
    const subdomain = hostname.split('.')[0];
    
    if (subdomain && subdomain !== 'www' && subdomain !== 'currently') {
      setCallSign(subdomain.toUpperCase());
      document.title = `Now Playing on ${subdomain.toUpperCase()}`;
    } else {
      document.title = 'Choose a Radio Station';
    }
  }, []);

  const {
    currentSong,
    history,
    isLoading,
    error,
    lastUpdated,
    isOffline,
    toggleStar,
    isSongStarred
  } = useNowPlaying(callSign);

  // Show station list if no valid call sign
  if (!callSign || callSign === 'WWW' || callSign === 'CURRENTLY') {
    return (
      <div className="container">
        <StationList />
      </div>
    );
  }

  // Show error if there's an error
  if (error) {
    return (
      <div className="container">
        <ErrorDisplay message={error} />
      </div>
    );
  }

  return (
    <div className="container">
      <OfflineIndicator isVisible={isOffline} />
      
      <NowPlaying
        song={currentSong?.song}
        artist={currentSong?.artist}
        spotifyUrl={currentSong?.spotifySearchUrl}
        stationName={callSign}
        isStarred={currentSong ? isSongStarred(`${currentSong.song}:::${currentSong.artist}`) : false}
        onToggleStar={toggleStar}
        lastUpdated={lastUpdated}
        isOffline={isOffline}
      />

      <History
        history={history}
        onToggleStar={toggleStar}
        isSongStarred={isSongStarred}
      />
    </div>
  );
}

export default App;