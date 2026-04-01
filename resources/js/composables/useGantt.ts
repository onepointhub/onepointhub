import { computed, ref } from 'vue'

export type ZoomLevel = 'week' | 'month' | 'quarter'

const COLUMN_WIDTH: Record<ZoomLevel, number> = {
  week: 120,
  month: 40,
  quarter: 14,
}

export function useGantt(projectStartsAt: string | null, projectEndsAt: string | null) {
  const zoom = ref<ZoomLevel>('month')

  const startDate = computed(() => {
    if (projectStartsAt) {
      return new Date(projectStartsAt)
    }
    const d = new Date()
    d.setDate(1)
    return d
  })

  const endDate = computed(() => {
    if (projectEndsAt) {
      return new Date(projectEndsAt)
    }
    const d = new Date(startDate.value)
    d.setMonth(d.getMonth() + 6)
    return d
  })

  const totalDays = computed(() =>
    Math.ceil((endDate.value.getTime() - startDate.value.getTime()) / (1000 * 60 * 60 * 24)),
  )

  const dayWidth = computed(() => COLUMN_WIDTH[zoom.value])

  const totalWidth = computed(() => totalDays.value * dayWidth.value)

  function dateToX(dateStr: string): number {
    const d = new Date(dateStr)
    const days = Math.floor((d.getTime() - startDate.value.getTime()) / (1000 * 60 * 60 * 24))
    return Math.max(0, days * dayWidth.value)
  }

  function durationToWidth(startsAt: string, endsAt: string): number {
    const start = new Date(startsAt)
    const end = new Date(endsAt)
    const days = Math.ceil((end.getTime() - start.getTime()) / (1000 * 60 * 60 * 24))
    return Math.max(dayWidth.value, days * dayWidth.value)
  }

  // Generate header columns based on zoom level
  const headerColumns = computed(() => {
    const cols: { label: string, x: number, width: number }[] = []
    const d = new Date(startDate.value)

    if (zoom.value === 'week') {
      while (d <= endDate.value) {
        const weekStart = new Date(d)
        const label = `${d.toLocaleDateString('en', { month: 'short', day: 'numeric' })}`
        cols.push({ label, x: dateToX(weekStart.toISOString().slice(0, 10)), width: dayWidth.value * 7 })
        d.setDate(d.getDate() + 7)
      }
    }
    else if (zoom.value === 'month') {
      while (d <= endDate.value) {
        const label = d.toLocaleDateString('en', { month: 'short', year: '2-digit' })
        const daysInMonth = new Date(d.getFullYear(), d.getMonth() + 1, 0).getDate()
        cols.push({ label, x: dateToX(d.toISOString().slice(0, 10)), width: daysInMonth * dayWidth.value })
        d.setMonth(d.getMonth() + 1)
        d.setDate(1)
      }
    }
    else {
      while (d <= endDate.value) {
        const q = Math.floor(d.getMonth() / 3) + 1
        const label = `Q${q} ${d.getFullYear()}`
        const daysInQuarter = 91 // approximate
        cols.push({ label, x: dateToX(d.toISOString().slice(0, 10)), width: daysInQuarter * dayWidth.value })
        d.setMonth(d.getMonth() + 3)
      }
    }

    return cols
  })

  return {
    zoom,
    startDate,
    endDate,
    totalWidth,
    dayWidth,
    headerColumns,
    dateToX,
    durationToWidth,
  }
}
