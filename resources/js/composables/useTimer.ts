import { computed, onUnmounted, ref } from 'vue'

const STORAGE_KEY = 'onepointhub_timer'

interface TimerState {
  projectId: number
  taskId: number | null
  startedAt: string // ISO string
  entryId: number | null
}

const state = ref<TimerState | null>(
  (() => {
    try {
      const raw = localStorage.getItem(STORAGE_KEY)
      return raw ? JSON.parse(raw) : null
    }
    catch {
      return null
    }
  })(),
)

const now = ref(Date.now())
let interval: ReturnType<typeof setInterval> | null = null

function startTicking() {
  if (interval) {
    return
  }
  interval = setInterval(() => {
    now.value = Date.now()
  }, 1000)
}

function stopTicking() {
  if (interval) {
    clearInterval(interval)
    interval = null
  }
}

if (state.value) {
  startTicking()
}

export function useTimer() {
  const isRunning = computed(() => state.value !== null)

  const elapsedSeconds = computed(() => {
    if (!state.value) {
      return 0
    }
    return Math.floor((now.value - new Date(state.value.startedAt).getTime()) / 1000)
  })

  const formattedElapsed = computed(() => {
    const s = elapsedSeconds.value
    const h = Math.floor(s / 3600)
    const m = Math.floor((s % 3600) / 60)
    const sec = s % 60
    return `${String(h).padStart(2, '0')}:${String(m).padStart(2, '0')}:${String(sec).padStart(2, '0')}`
  })

  function start(projectId: number, taskId: number | null, entryId: number | null) {
    state.value = { projectId, taskId, startedAt: new Date().toISOString(), entryId }
    localStorage.setItem(STORAGE_KEY, JSON.stringify(state.value))
    startTicking()
  }

  function stop() {
    state.value = null
    localStorage.removeItem(STORAGE_KEY)
    stopTicking()
  }

  onUnmounted(() => {
    // Don't stop the global interval on component unmounted
  })

  return {
    isRunning,
    elapsedSeconds,
    formattedElapsed,
    timerState: state,
    start,
    stop,
  }
}
