<script setup lang="ts">
import { onMounted, computed, ref } from 'vue'
import { useRoute } from 'vue-router'
import { useTeamStore } from '@/stores/team'
import { useAuthStore } from '@/stores/auth'
import { useUiStore } from '@/stores/ui'
import AppLayout from '@/components/layouts/AppLayout.vue'
import BaseCard from '@/components/ui/BaseCard.vue'
import BaseButton from '@/components/ui/BaseButton.vue'
import BaseInput from '@/components/ui/BaseInput.vue'
import BaseModal from '@/components/ui/BaseModal.vue'
import BaseSelect from '@/components/ui/BaseSelect.vue'
import BaseAvatar from '@/components/ui/BaseAvatar.vue'
import BaseSkeleton from '@/components/ui/BaseSkeleton.vue'
import type { Role } from '@/types/models'

const route = useRoute()
const teamStore = useTeamStore()
const auth = useAuthStore()
const ui = useUiStore()

const teamSlug = computed(() => route.params.team as string)
const showInvite = ref(false)
const isLoading = ref(true)
const inviteEmail = ref('')
const inviteRole = ref<Role>('member')
const isInviting = ref(false)

onMounted(async () => {
  try {
    await teamStore.fetchMembers(teamSlug.value)
  } finally {
    isLoading.value = false
  }
})

async function invite() {
  if (!inviteEmail.value) return
  isInviting.value = true
  try {
    await teamStore.inviteMember(teamSlug.value, inviteEmail.value, inviteRole.value)
    ui.toast(`Invitation sent to ${inviteEmail.value}`)
    showInvite.value = false
    inviteEmail.value = ''
  } catch {
    ui.toast('Failed to send invitation', 'error')
  } finally {
    isInviting.value = false
  }
}

async function changeRole(memberId: number, role: Role) {
  await teamStore.updateMember(teamSlug.value, memberId, role)
  ui.toast('Role updated')
}

async function removeMember(memberId: number) {
  if (!confirm('Remove this member from the team?')) return
  await teamStore.removeMember(teamSlug.value, memberId)
  ui.toast('Member removed')
}
</script>

<template>
  <AppLayout>
    <div class="p-6 max-w-3xl">
      <div class="flex items-center justify-between mb-6">
        <div>
          <h1 class="text-2xl font-bold text-[var(--color-text-primary)]">Members</h1>
          <p class="text-sm text-[var(--color-text-secondary)] mt-0.5">{{ teamStore.members.length }} members</p>
        </div>
        <BaseButton v-if="teamStore.canManage" size="sm" @click="showInvite = true">Invite member</BaseButton>
      </div>

      <BaseCard class="divide-y divide-[var(--color-border)]">
        <template v-if="isLoading">
          <div v-for="i in 4" :key="i" class="flex items-center gap-3 p-4">
            <BaseSkeleton class="w-9 h-9 rounded-full" />
            <div class="flex-1 space-y-1.5">
              <BaseSkeleton class="h-3.5 w-32 rounded" />
              <BaseSkeleton class="h-3 w-48 rounded" />
            </div>
          </div>
        </template>
        <div v-else v-for="member in teamStore.members" :key="member.id" class="flex items-center gap-3 p-4">
          <BaseAvatar :user="member.user" size="sm" />
          <div class="flex-1 min-w-0">
            <p class="text-sm font-medium text-[var(--color-text-primary)]">
              {{ member.user.name }}
              <span v-if="member.user.id === auth.user?.id" class="text-xs text-[var(--color-text-secondary)] ml-1">(you)</span>
            </p>
            <p class="text-xs text-[var(--color-text-secondary)]">{{ member.user.email }}</p>
          </div>
          <div v-if="teamStore.canManage && member.role !== 'owner'" class="flex items-center gap-2">
            <select
              :value="member.role"
              @change="changeRole(member.id, ($event.target as HTMLSelectElement).value as Role)"
              class="text-xs border border-[var(--color-border)] rounded-lg px-2 py-1 bg-[var(--color-bg-elevated)] text-[var(--color-text-secondary)] focus:outline-none focus:border-indigo-500"
            >
              <option value="admin">Admin</option>
              <option value="member">Member</option>
              <option value="viewer">Viewer</option>
            </select>
            <button
              v-if="member.user.id !== auth.user?.id"
              @click="removeMember(member.id)"
              class="text-xs text-red-500 hover:text-red-600 px-2 py-1 rounded transition-colors"
            >Remove</button>
          </div>
          <span v-else class="text-xs capitalize text-[var(--color-text-secondary)] bg-[var(--color-bg-elevated)] px-2.5 py-1 rounded-full">{{ member.role }}</span>
        </div>
      </BaseCard>
    </div>

    <BaseModal :show="showInvite" title="Invite member" @close="showInvite = false">
      <form @submit.prevent="invite" class="space-y-4">
        <BaseInput v-model="inviteEmail" type="email" label="Email address" placeholder="teammate@company.com" required autofocus />
        <BaseSelect v-model="inviteRole" label="Role">
          <option value="admin">Admin</option>
          <option value="member">Member</option>
          <option value="viewer">Viewer</option>
        </BaseSelect>
        <div class="flex justify-end gap-3 pt-2">
          <BaseButton type="button" variant="ghost" @click="showInvite = false">Cancel</BaseButton>
          <BaseButton type="submit" :loading="isInviting">Send invite</BaseButton>
        </div>
      </form>
    </BaseModal>
  </AppLayout>
</template>
