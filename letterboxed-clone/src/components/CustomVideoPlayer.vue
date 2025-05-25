<template>
  <div class="video-player">
    <div class="video-container" @click="togglePlay">
      <video
        ref="videoRef"
        @timeupdate="updateProgress"
        @loadedmetadata="initializeVideo"
        :src="videoSrc"
        class="video-element"
      ></video>
      
      <!-- Play/Pause Overlay Icon -->
      <div class="play-overlay" v-if="!isPlaying">
        <div class="play-icon">
          <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor">
            <path d="M8 5v14l11-7z"/>
          </svg>
        </div>
      </div>
    </div>
    
    <div class="controls">
      <!-- Play/Pause Button -->
      <button class="control-button" @click="togglePlay">
        <svg v-if="!isPlaying" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M8 5v14l11-7z"/>
        </svg>
        <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
          <path d="M6 19h4V5H6v14zm8-14v14h4V5h-4z"/>
        </svg>
      </button>
      
      <!-- Progress Bar -->
      <div 
        class="progress-container"
        @click="seek"
        @mousemove="updateSeekPreview($event)"
        @mouseenter="isSeekHovering = true"
        @mouseleave="isSeekHovering = false"
      >
        <div class="progress-bar">
          <div class="progress-fill" :style="{ width: `${progress}%` }"></div>
          <div 
            v-if="isSeekHovering" 
            class="seek-preview" 
            :style="{ left: `${seekPreviewPosition}px` }"
          ></div>
        </div>
      </div>
      
      <!-- Time Display -->
      <div class="time-display">
        {{ formatTime(currentTime) }} / {{ formatTime(duration) }}
      </div>
      
      <!-- Volume Control -->
      <div class="volume-control">
        <button class="control-button" @click="toggleMute">
          <svg v-if="isMuted || volume === 0" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M16.5 12c0-1.77-1.02-3.29-2.5-4.03v2.21l2.45 2.45c.03-.2.05-.41.05-.63zm2.5 0c0 .94-.2 1.82-.54 2.64l1.51 1.51C20.63 14.91 21 13.5 21 12c0-4.28-2.99-7.86-7-8.77v2.06c2.89.86 5 3.54 5 6.71zM4.27 3L3 4.27 7.73 9H3v6h4l5 5v-6.73l4.25 4.25c-.67.52-1.42.93-2.25 1.18v2.06c1.38-.31 2.63-.95 3.69-1.81L19.73 21 21 19.73l-9-9L4.27 3zM12 4L9.91 6.09 12 8.18V4z"/>
          </svg>
          <svg v-else-if="volume < 0.5" width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M18.5 12c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM5 9v6h4l5 5V4L9 9H5z"/>
          </svg>
          <svg v-else width="24" height="24" viewBox="0 0 24 24" fill="currentColor">
            <path d="M3 9v6h4l5 5V4L7 9H3zm13.5 3c0-1.77-1.02-3.29-2.5-4.03v8.05c1.48-.73 2.5-2.25 2.5-4.02zM14 3.23v2.06c2.89.86 5 3.54 5 6.71s-2.11 5.85-5 6.71v2.06c4.01-.91 7-4.49 7-8.77s-2.99-7.86-7-8.77z"/>
          </svg>
        </button>
        <input 
          type="range" 
          min="0" 
          max="1" 
          step="0.1" 
          v-model="volume" 
          @input="updateVolume"
          class="volume-slider"
        />
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';

export default {
  name: 'CustomVideoPlayer',
  props: {
    videoSrc: {
      type: String,
      required: true
    }
  },
  
  setup(props) {
    const videoRef = ref(null);
    const isPlaying = ref(false);
    const progress = ref(0);
    const currentTime = ref(0);
    const duration = ref(0);
    const volume = ref(1);
    const isMuted = ref(false);
    const isSeekHovering = ref(false);
    const seekPreviewPosition = ref(0);
    
    // Initialize video when metadata is loaded
    const initializeVideo = () => {
      if (videoRef.value) {
        duration.value = videoRef.value.duration;
      }
    };
    
    // Update progress bar and current time
    const updateProgress = () => {
      if (videoRef.value) {
        currentTime.value = videoRef.value.currentTime;
        progress.value = (currentTime.value / duration.value) * 100;
      }
    };
    
    // Toggle play/pause
    const togglePlay = () => {
      if (videoRef.value) {
        if (isPlaying.value) {
          videoRef.value.pause();
        } else {
          videoRef.value.play();
        }
        isPlaying.value = !isPlaying.value;
      }
    };
    
    // Seek to position when clicking on progress bar
    const seek = (event) => {
      if (videoRef.value) {
        const progressContainer = event.currentTarget;
        const rect = progressContainer.getBoundingClientRect();
        const seekPosition = (event.clientX - rect.left) / rect.width;
        videoRef.value.currentTime = seekPosition * duration.value;
        updateProgress();
      }
    };
    
    // Update volume
    const updateVolume = () => {
      if (videoRef.value) {
        videoRef.value.volume = volume.value;
        if (volume.value > 0) {
          isMuted.value = false;
          videoRef.value.muted = false;
        } else {
          isMuted.value = true;
          videoRef.value.muted = true;
        }
      }
    };
    
    // Toggle mute
    const toggleMute = () => {
      if (videoRef.value) {
        isMuted.value = !isMuted.value;
        videoRef.value.muted = isMuted.value;
      }
    };
    
    // Format time in MM:SS format
    const formatTime = (timeInSeconds) => {
      if (isNaN(timeInSeconds)) return '00:00';
      
      const minutes = Math.floor(timeInSeconds / 60);
      const seconds = Math.floor(timeInSeconds % 60);
      
      return `${minutes.toString().padStart(2, '0')}:${seconds.toString().padStart(2, '0')}`;
    };
    
    // Update seek preview position
    const updateSeekPreview = (event) => {
      const progressContainer = event.currentTarget;
      const rect = progressContainer.getBoundingClientRect();
      seekPreviewPosition.value = event.clientX - rect.left;
    };
    
    // Watch for changes to the video source
    watch(() => props.videoSrc, () => {
      // Reset player state when source changes
      if (videoRef.value) {
        currentTime.value = 0;
        progress.value = 0;
        isPlaying.value = false;
      }
    });
    
    return {
      videoRef,
      isPlaying,
      progress,
      currentTime,
      duration,
      volume,
      isMuted,
      isSeekHovering,
      seekPreviewPosition,
      initializeVideo,
      updateProgress,
      togglePlay,
      seek,
      updateVolume,
      toggleMute,
      formatTime,
      updateSeekPreview
    };
  }
};
</script>

<style scoped>
.video-player {
  width: 100%;
  max-width: 800px;
  margin: 0 auto;
  background-color: #000;
  border-radius: 8px;
  overflow: hidden;
  box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
}

.video-container {
  position: relative;
  width: 100%;
  cursor: pointer;
}

.video-element {
  width: 100%;
  display: block;
}

.play-overlay {
  position: absolute;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  background-color: rgba(0, 0, 0, 0.3);
}

.play-icon {
  font-size: 48px;
  color: white;
}

.controls {
  display: flex;
  align-items: center;
  padding: 10px;
  background-color: #222;
  color: white;
}

.control-button {
  background: none;
  border: none;
  color: white;
  font-size: 18px;
  cursor: pointer;
  padding: 0 10px;
}

.progress-container {
  flex-grow: 1;
  height: 20px;
  margin: 0 10px;
  cursor: pointer;
  display: flex;
  flex-direction: column;
  justify-content: center;

}

.progress-bar {
  background-color: #444;
  height: 6px;
  border-radius: 3px;
  overflow: hidden;
  position: relative;
}

.progress-fill {
  background-color: #667eea;
  height: 100%;
  width: 0;
  transition: width 0.1s linear;
}

.seek-preview {
  position: absolute;
  width: 2px;
  height: 10px;
  background-color: #fff;
  top: -2px;
}

.time-display {
  font-size: 14px;
  margin: 0 10px;
  min-width: 100px;
  text-align: center;
}

.volume-control {
  display: flex;
  align-items: center;
}

.volume-slider {
  width: 80px;
  cursor: pointer;
}

/* Mobile responsiveness */
@media (max-width: 600px) {
  .time-display {
    display: none;
  }
  
  .volume-slider {
    width: 50px;
  }
  
  .controls {
    padding: 5px;
  }
}
</style>
