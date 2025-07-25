import React, { useState } from 'react';
import './History.css';

const History = ({ history, starredSongs, onToggleStar, isSongStarred }) => {
  const [showingStarredOnly, setShowingStarredOnly] = useState(false);
  const [currentPage, setCurrentPage] = useState(1);
  const ITEMS_PER_PAGE = 10;

  const filteredHistory = showingStarredOnly 
    ? history.filter(item => isSongStarred(`${item.song}:::${item.artist}`))
    : history;

  const totalPages = Math.ceil(filteredHistory.length / ITEMS_PER_PAGE);
  const paginatedHistory = filteredHistory.slice(
    (currentPage - 1) * ITEMS_PER_PAGE,
    currentPage * ITEMS_PER_PAGE
  );

  const formatTime = (timestamp) => {
    const time = new Date(timestamp);
    const now = new Date();
    
    if (time.toDateString() === now.toDateString()) {
      return time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    } else {
      return time.toLocaleDateString([], { month: 'short', day: 'numeric' }) + 
             ' ' + time.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    }
  };

  // Reset to page 1 when filter changes
  React.useEffect(() => {
    setCurrentPage(1);
  }, [showingStarredOnly, history]);

  if (history.length === 0) {
    return null;
  }

  return (
    <div className="history-section">
      <div className="history-header">
        <h5>Previously Played</h5>
        <button 
          className="filter-button"
          onClick={() => setShowingStarredOnly(!showingStarredOnly)}
        >
          {showingStarredOnly ? 'Show All' : 'Show Starred Only'}
        </button>
      </div>
      
      <div className="history-container">
        {paginatedHistory.map((item, index) => {
          const songKey = `${item.song}:::${item.artist}`;
          const isStarred = isSongStarred(songKey);
          
          return (
            <div 
              key={`${songKey}-${item.timestamp}`}
              className="history-card"
              style={{ animationDelay: `${index * 0.05}s` }}
            >
              <div className="history-content">
                <div className="history-info">
                  <h6 className="history-title">{item.song}</h6>
                  <p className="history-artist">{item.artist}</p>
                  <p className="history-time">{formatTime(item.timestamp)}</p>
                </div>
                <div className="history-actions">
                  <button 
                    className={`star-button ${isStarred ? 'starred' : ''}`}
                    onClick={() => onToggleStar(songKey)}
                  >
                    ★
                  </button>
                  <a href={item.spotifyUrl} target="_blank" rel="noopener noreferrer">
                    <img src="/thirdParty/spotify.svg" className="history-spotify" alt="Open in Spotify" />
                  </a>
                </div>
              </div>
            </div>
          );
        })}
      </div>
      {totalPages > 1 && (
        <div className="history-pagination" style={{ display: 'flex', justifyContent: 'center', marginTop: '1rem', gap: '1rem' }}>
          <button
            className="filter-button"
            onClick={() => setCurrentPage(p => Math.max(1, p - 1))}
            disabled={currentPage === 1}
          >
            Previous
          </button>
          <span style={{ alignSelf: 'center' }}>Page {currentPage} of {totalPages}</span>
          <button
            className="filter-button"
            onClick={() => setCurrentPage(p => Math.min(totalPages, p + 1))}
            disabled={currentPage === totalPages}
          >
            Next
          </button>
        </div>
      )}
    </div>
  );
};

export default History;