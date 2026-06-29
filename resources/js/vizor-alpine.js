/**
 * Vizor Alpine.js Plugin
 *
 * Provides two data components:
 * - vizorPlayer: standalone Alpine.js player data binding
 * - vizorLivewirePlayer: Livewire bridge for reactive server-side state
 */
export default function vizorAlpine(Alpine) {
    // ──────────────────────── Standalone Player ────────────────────────
    Alpine.data('vizorPlayer', () => ({
        ready: false,
        playing: false,
        currentTime: 0,
        duration: 0,
        volume: 1,
        muted: false,
        fullscreen: false,
        loading: false,
        error: null,

        init() {
            const el = this.$refs.player;
            if (!el) return;

            el.addEventListener('vz-ready', () => {
                this.ready = true;
                this.loading = false;
            });
            el.addEventListener('play', () => { this.playing = true; });
            el.addEventListener('pause', () => { this.playing = false; });
            el.addEventListener('ended', () => { this.playing = false; });
            el.addEventListener('timeupdate', () => {
                this.currentTime = el.currentTime || 0;
                this.duration = el.duration || 0;
            });
            el.addEventListener('volumechange', () => {
                this.volume = el.volume ?? 1;
                this.muted = el.muted ?? false;
            });
            el.addEventListener('vz-fullscreen-enter', () => { this.fullscreen = true; });
            el.addEventListener('vz-fullscreen-exit', () => { this.fullscreen = false; });
            el.addEventListener('vz-loading-start', () => { this.loading = true; });
            el.addEventListener('vz-loading-complete', () => { this.loading = false; });
            el.addEventListener('vz-error', (e) => {
                this.error = e.detail || { code: 'UNKNOWN', message: 'Unknown error' };
            });
        },

        play() { this.$refs.player?.play?.(); },
        pause() { this.$refs.player?.pause?.(); },
        togglePlay() { this.playing ? this.pause() : this.play(); },
        seek(time) { this.$refs.player?.seek?.(time); },
        toggleMute() { this.$refs.player?.toggleMute?.(); },
        setVolume(vol) {
            if (this.$refs.player) {
                this.$refs.player.volume = vol;
            }
        },
        enterFullscreen() { this.$refs.player?.requestFullscreen?.(); },
        exitFullscreen() { document.exitFullscreen?.(); },
    }));

    // ──────────────────────── Livewire Bridge ────────────────────────
    Alpine.data('vizorLivewirePlayer', (wire) => ({
        init() {
            const el = this.$refs.player;
            if (!el) return;

            // Forward DOM events to Livewire component methods
            el.addEventListener('play', () => wire.onPlay());
            el.addEventListener('pause', () => wire.onPause());
            el.addEventListener('ended', () => wire.onEnded());
            el.addEventListener('timeupdate', () => {
                wire.onTimeUpdate(el.currentTime || 0, el.duration || 0);
            });
            el.addEventListener('volumechange', () => {
                wire.onVolumeChange(el.volume ?? 1, el.muted ?? false);
            });
            el.addEventListener('vz-ready', () => wire.onReady());
            el.addEventListener('vz-error', (e) => {
                wire.onError(e.detail?.code || 'UNKNOWN', e.detail?.message || 'Unknown error');
            });

            // Listen for Livewire commands dispatched from server-side actions
            if (typeof Livewire !== 'undefined') {
                Livewire.on('vizor-command', ({ command, time, volume }) => {
                    if (command === 'play') el.play?.();
                    if (command === 'pause') el.pause?.();
                    if (command === 'seek' && time != null) el.seek?.(time);
                    if (command === 'setVolume' && volume != null) el.volume = volume;
                    if (command === 'toggleMute') el.toggleMute?.();
                });
            }
        },
    }));
}
