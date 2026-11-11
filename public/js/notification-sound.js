/**
 * Konfigurasi nada notifikasi sintetis.
 * Untuk mengubah nada, atur frekuensi (Hz), waktu mulai/durasi (detik),
 * bentuk gelombang, dan volume di TONES.
 */
(function () {
    'use strict';

    const TONES = {
        success: {
            volume: 0.2,
            notes: [
                { frequency: 523.25, start: 0, duration: 0.16, wave: 'sine' },
                { frequency: 1000.25, start: 0.12, duration: 0.16, wave: 'sine' },
                { frequency: 783.99, start: 0.24, duration: 0.28, wave: 'sine' },
            ],
        },
        error: {
            volume: 0.4,
            notes: [
                { frequency: 523.25, start: 0, duration: 0.16, wave: 'sine' },
                { frequency: 493.88, start: 0.12, duration: 0.16, wave: 'sine' },
                { frequency: 466.16, start: 0.24, duration: 0.28, wave: 'sine' },
            ],
        },
        info: {
            volume: 0.16,
            notes: [
                { frequency: 587.33, start: 0, duration: 0.22, wave: 'sine' },
            ],
        },
        warning: {
            volume: 0.16,
            notes: [
                { frequency: 523.25, start: 0, duration: 0.16, wave: 'sine' },
                { frequency: 523.25, start: 0.12, duration: 0.16, wave: 'sine' },
                { frequency: 523.25, start: 0.24, duration: 0.28, wave: 'sine' },
            ],
        },
    };
    let audioContext = null;

    function playNote(context, note, volume) {
        const oscillator = context.createOscillator();
        const gain = context.createGain();
        const startAt = context.currentTime + note.start;
        const endAt = startAt + note.duration;

        oscillator.type = note.wave;
        oscillator.frequency.setValueAtTime(note.frequency, startAt);
        gain.gain.setValueAtTime(0.0001, startAt);
        gain.gain.exponentialRampToValueAtTime(volume, startAt + 0.02);
        gain.gain.exponentialRampToValueAtTime(0.0001, endAt);
        oscillator.connect(gain);
        gain.connect(context.destination);
        oscillator.start(startAt);
        oscillator.stop(endAt + 0.03);
    }

    async function play(type = 'info') {
        const setting = TONES[type] || TONES.info;

        try {
            const AudioContext = window.AudioContext || window.webkitAudioContext;
            if (!AudioContext) return;

            audioContext = audioContext || new AudioContext();
            if (audioContext.state === 'suspended') await audioContext.resume();

            setting.notes.forEach((note) => playNote(audioContext, note, setting.volume));
        } catch (error) {
            // Notifikasi visual tetap berfungsi jika audio tidak tersedia.
        }
    }

    window.NotificationSound = { play };
})();
