<script setup lang="ts">
import { computed } from 'vue'
import { generateInitials, generateAvatarColor } from '@/lib/utils'
import type { User } from '@/types/models'

const props = defineProps<{
  user?: User | null
  name?: string
  size?: 'xs' | 'sm' | 'md' | 'lg'
  id?: number
}>()

const sizeClass = computed(() => ({
  xs: 'w-5 h-5 text-[10px]',
  sm: 'w-7 h-7 text-xs',
  md: 'w-8 h-8 text-sm',
  lg: 'w-10 h-10 text-base',
})[props.size ?? 'md'])

const displayName = computed(() => props.user?.name ?? props.name ?? '?')
const initials = computed(() => generateInitials(displayName.value))
const colorClass = computed(() => generateAvatarColor(props.user?.id ?? props.id ?? 0))
</script>

<template>
  <div :class="[sizeClass, 'relative rounded-full flex-shrink-0 overflow-hidden']">
    <img v-if="user?.avatar_url" :src="user.avatar_url" :alt="displayName" class="w-full h-full object-cover" />
    <div v-else :class="[sizeClass, colorClass, 'flex items-center justify-center text-white font-semibold rounded-full']">
      {{ initials }}
    </div>
  </div>
</template>
