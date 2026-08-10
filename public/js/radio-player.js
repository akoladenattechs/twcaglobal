// Radio Player Functionality with Cloudflare R2 Streaming and Persistence
// Base URL for API calls (supports subdirectory installs like /bli-laravel)
var API_BASE = window.BASE_URL || '';

document.addEventListener('DOMContentLoaded', function () {
    const playBtn = document.getElementById('radioPlayBtn');

    // Sometimes the ID is on the button, sometimes on the icon depending on implementing
    // Let's ensure we target the icon for class changes
    let playIcon = document.getElementById('playIcon');
    if (!playIcon && playBtn) {
        playIcon = playBtn.querySelector('i') || playBtn.querySelector('span');
    }

    const radioStream = document.getElementById('radioStream');
    const volumeSlider = document.querySelector('.volume-slider');
    const currentSermonTitle = document.getElementById('currentSermonTitle');
    const currentPreacher = document.getElementById('currentPreacher');

    // UI Loading indicator helper
    // Helper to restore the current sermon's info in the UI
    const restoreSermonInfo = () => {
        if (currentPlaylist.length > 0 && currentPlaylist[currentIndex]) {
            const sermon = currentPlaylist[currentIndex];
            if (currentSermonTitle) currentSermonTitle.textContent = sermon.title || 'No Radio Content';
            if (currentPreacher) currentPreacher.textContent = sermon.preacher || sermon.artist || '';
        }
    };

    const setLoading = (isLoading) => {
        if (!playBtn) return;
        if (isLoading) {
            playBtn.classList.add('loading');
            playBtn.disabled = true;
            if (currentPreacher) currentPreacher.textContent = 'Buffering...';
        } else {
            playBtn.classList.remove('loading');
            playBtn.disabled = false;
            // Restore sermon info when loading completes
            restoreSermonInfo();
        }
    };

    // State keys for sessionStorage
    const STORAGE_KEYS = {
        PLAYLIST: 'twca_radio_playlist',
        INDEX: 'twca_radio_index',
        TIME: 'twca_radio_time',
        IS_PLAYING: 'twca_radio_status', // 'playing' or 'paused'
        VOLUME: 'twca_radio_volume',
        LAST_ACTIVE: 'twca_radio_last_active'
    };

    let currentPlaylist = [];
    let currentIndex = 0;
    let seekTime = 0; // Store seek time for metadata load
    let retryCount = 0;
    const MAX_RETRIES = 3;

    // Initialize Player
    initPlayer();

    function initPlayer() {
        // Restore volume
        const savedVolume = parseFloat(sessionStorage.getItem(STORAGE_KEYS.VOLUME));
        if (!isNaN(savedVolume) && radioStream) {
            radioStream.volume = savedVolume;
            if (volumeSlider) volumeSlider.value = savedVolume * 100;
        }

        // Check active session (30 mins timeout)
        const lastActive = parseInt(sessionStorage.getItem(STORAGE_KEYS.LAST_ACTIVE));
        const now = Date.now();
        const sessionTimeout = 30 * 60 * 1000;

        if (lastActive && (now - lastActive) < sessionTimeout) {
            // Restore session
            try {
                const storedPlaylist = JSON.parse(sessionStorage.getItem(STORAGE_KEYS.PLAYLIST));
                if (storedPlaylist && storedPlaylist.length > 0) {
                    currentPlaylist = storedPlaylist;
                    currentIndex = parseInt(sessionStorage.getItem(STORAGE_KEYS.INDEX) || '0');
                    seekTime = parseFloat(sessionStorage.getItem(STORAGE_KEYS.TIME) || '0');

                    // Validate index
                    if (currentIndex >= currentPlaylist.length) currentIndex = 0;

                    loadCurrentSermon();

                    // Resume only if it was playing, but check connection first?
                    // No, just try to play if it was playing.
                    if (sessionStorage.getItem(STORAGE_KEYS.IS_PLAYING) === 'playing') {
                        playAudio();
                    } else {
                        updatePlayButtonState(false);
                    }
                } else {
                    fetchNewPlaylist();
                }
            } catch (e) {
                console.error('Error restoring session:', e);
                fetchNewPlaylist();
            }
        } else {
            // New session
            sessionStorage.clear();
            fetchNewPlaylist();
        }

        // Update activity timestamp
        setInterval(() => {
            sessionStorage.setItem(STORAGE_KEYS.LAST_ACTIVE, Date.now());
        }, 30000);

        // Save time frequently
        setInterval(() => {
            if (radioStream && !radioStream.paused) {
                sessionStorage.setItem(STORAGE_KEYS.TIME, radioStream.currentTime);
            }
        }, 1000);
    }

    async function fetchNewPlaylist() {
        updateUIState('loading');
        try {
            // Add timestamp to prevent caching
            const response = await fetch(API_BASE + '/api/radio?action=playlist&t=' + Date.now());
            const data = await response.json();

            if (data.success && data.sermons && data.sermons.length > 0) {
                currentPlaylist = data.sermons;
                currentIndex = 0;
                seekTime = 0;

                // Save initial state
                sessionStorage.setItem(STORAGE_KEYS.PLAYLIST, JSON.stringify(currentPlaylist));
                sessionStorage.setItem(STORAGE_KEYS.INDEX, currentIndex);
                sessionStorage.setItem(STORAGE_KEYS.TIME, 0);
                // Don't auto-play on fresh visit unless user interaction (browser policy)
                // But we can set state to ready.

                loadCurrentSermon();
                updateUIState('ready');
                // Ensure UI shows the current sermon info
                restoreSermonInfo();
            } else {
                updateUIState('empty');
            }
        } catch (error) {
            console.error('Error fetching playlist:', error);
            updateUIState('error');
        }
    }

    function loadCurrentSermon() {
        if (!currentPlaylist.length || !radioStream) return;
        const sermon = currentPlaylist[currentIndex];

        radioStream.src = sermon.url;
        radioStream.load();

        // Update UI
        if (currentSermonTitle) currentSermonTitle.textContent = sermon.title;
        if (currentPreacher) currentPreacher.textContent = sermon.preacher || sermon.artist || '';
    }

    function playAudio() {
        if (!radioStream) return;

        setLoading(true);

        const playPromise = radioStream.play();
        if (playPromise !== undefined) {
            playPromise.then(() => {
                setLoading(false);
                updatePlayButtonState(true);
                sessionStorage.setItem(STORAGE_KEYS.IS_PLAYING, 'playing');
            }).catch(error => {
                setLoading(false);
                // Only log real errors, ignore AbortError which happens on rapid clicking
                if (error.name !== 'AbortError') {
                    console.log('Playback prevented:', error);
                }
                updatePlayButtonState(false);
                sessionStorage.setItem(STORAGE_KEYS.IS_PLAYING, 'paused');
            });
        }
    }

    function pauseAudio() {
        if (!radioStream) return;
        radioStream.pause();
        updatePlayButtonState(false);
        sessionStorage.setItem(STORAGE_KEYS.IS_PLAYING, 'paused');
    }

    function togglePlay() {
        if (!radioStream) return;
        if (radioStream.paused) {
            playAudio();
        } else {
            pauseAudio();
        }
    }

    function playNext(shouldPlay = true) {
        if (currentPlaylist.length === 0) return;

        currentIndex = (currentIndex + 1) % currentPlaylist.length;
        seekTime = 0;

        sessionStorage.setItem(STORAGE_KEYS.INDEX, currentIndex);
        sessionStorage.setItem(STORAGE_KEYS.TIME, 0);

        loadCurrentSermon();
        if (shouldPlay) playAudio();
    }

    function updatePlayButtonState(playing) {
        if (!playBtn) return;

        if (playing) {
            playBtn.classList.add('playing');
            if (playIcon) {
                playIcon.className = 'fa fa-pause';
                // Also support simple text if icon missing
                if (!playIcon.className) playBtn.innerHTML = '<i class="fa fa-pause"></i>';
            }
        } else {
            playBtn.classList.remove('playing');
            if (playIcon) {
                playIcon.className = 'fa fa-play';
                if (!playIcon.className) playBtn.innerHTML = '<i class="fa fa-play"></i>';
            }
        }
    }

    function updateUIState(state) {
        if (state === 'loading') {
            if (currentSermonTitle) currentSermonTitle.textContent = 'Loading Playlist...';
            if (currentPreacher) currentPreacher.textContent = 'Please wait';
            setLoading(true);
        } else if (state === 'ready') {
            setLoading(false);
        } else if (state === 'error') {
            if (currentSermonTitle) currentSermonTitle.textContent = 'Network Error';
            if (currentPreacher) currentPreacher.textContent = 'Retrying...';
            setTimeout(fetchNewPlaylist, 5000);
        } else if (state === 'empty') {
            if (currentSermonTitle) currentSermonTitle.textContent = 'No Radio Content';
        }
    }

    // --- Events ---

    if (playBtn) playBtn.addEventListener('click', togglePlay);

    if (volumeSlider) {
        volumeSlider.addEventListener('input', function () {
            if (radioStream) {
                radioStream.volume = this.value / 100;
                sessionStorage.setItem(STORAGE_KEYS.VOLUME, radioStream.volume);
            }
        });
    }

    if (radioStream) {
        // Handle seeking
        radioStream.addEventListener('loadedmetadata', function () {
            if (seekTime > 0) {
                radioStream.currentTime = seekTime;
            }
        });

        // When stream can play, remove loading and reset error count
        radioStream.addEventListener('canplay', function () {
            retryCount = 0;
            setLoading(false);
        });

        // When waiting for data (buffering)
        radioStream.addEventListener('waiting', function () {
            setLoading(true);
        });

        radioStream.addEventListener('ended', () => playNext(true));

        radioStream.addEventListener('error', function (e) {
            console.error('Audio stream error', e);
            setLoading(false);

            // Show error in UI
            if (currentPreacher) currentPreacher.textContent = 'Stream Error - Skipping...';

            // Should we auto-play the next one? 
            // Only if we were previously playing AND not blocked by browser
            const wasPlaying = !radioStream.paused;

            // Retry limit to avoid infinite loops on full network down
            if (retryCount < MAX_RETRIES) {
                retryCount++;
                setTimeout(() => {
                    if (currentPlaylist.length > 1) {
                        // Pass wasPlaying state to playNext
                        // If we weren't playing (or were blocked), this will just load the next song without forcing play
                        playNext(wasPlaying);
                    }
                }, 3000);
            } else {
                if (currentSermonTitle) currentSermonTitle.textContent = 'Connection Failed';
                if (currentPreacher) currentPreacher.textContent = 'Please check your internet';
                updatePlayButtonState(false);
            }
        });
    }

    // Save state on unload
    window.addEventListener('beforeunload', function () {
        if (radioStream) {
            sessionStorage.setItem(STORAGE_KEYS.TIME, radioStream.currentTime);
            sessionStorage.setItem(STORAGE_KEYS.IS_PLAYING, !radioStream.paused ? 'playing' : 'paused');
        }
        sessionStorage.setItem(STORAGE_KEYS.LAST_ACTIVE, Date.now());
    });
});