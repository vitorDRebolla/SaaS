<script setup lang="ts">
import { RouterLink } from 'vue-router'
</script>

<template>
  <div class="min-h-screen bg-zinc-950 text-white">
    <!-- Nav -->
    <nav class="flex items-center justify-between px-8 py-5 max-w-7xl mx-auto">
      <div class="flex items-center gap-2.5">
        <div class="w-8 h-8 rounded-xl bg-indigo-600 flex items-center justify-center font-bold text-sm">M</div>
        <span class="font-semibold text-lg tracking-tight">Meridian</span>
      </div>
      <div class="flex items-center gap-3">
        <RouterLink to="/login" class="text-sm text-zinc-400 hover:text-white transition-colors px-4 py-2">Sign in</RouterLink>
        <RouterLink to="/register" class="text-sm bg-indigo-600 hover:bg-indigo-500 transition-colors px-4 py-2 rounded-lg font-medium">Get started</RouterLink>
      </div>
    </nav>

    <!-- Hero -->
    <section class="max-w-5xl mx-auto px-8 pt-24 pb-32 text-center">
      <div class="inline-flex items-center gap-2 bg-indigo-600/10 border border-indigo-600/20 text-indigo-400 text-xs font-medium px-3 py-1.5 rounded-full mb-8">
        <span class="w-1.5 h-1.5 rounded-full bg-indigo-400 animate-pulse"></span>
        Team Project Intelligence Platform
      </div>
      <h1 class="text-5xl md:text-7xl font-bold tracking-tight leading-[1.08] mb-6">
        Ship faster.<br />
        <span class="text-transparent bg-clip-text bg-gradient-to-r from-indigo-400 to-violet-400">Together.</span>
      </h1>
      <p class="text-lg text-zinc-400 max-w-2xl mx-auto mb-10 leading-relaxed">
        Meridian combines issue tracking, project analytics, and team automation in one beautifully designed workspace. Built for teams that care about craft.
      </p>
      <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
        <RouterLink to="/register" class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-500 transition-colors px-8 py-3.5 rounded-xl font-semibold text-base">
          Start for free
        </RouterLink>
        <RouterLink to="/login" class="w-full sm:w-auto border border-zinc-700 hover:border-zinc-500 transition-colors px-8 py-3.5 rounded-xl font-semibold text-base text-zinc-300">
          Sign in to workspace
        </RouterLink>
      </div>
    </section>

    <!-- Features grid -->
    <section class="max-w-6xl mx-auto px-8 pb-32">
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div v-for="f in features" :key="f.title" class="bg-zinc-900 border border-zinc-800 rounded-2xl p-6">
          <div class="text-2xl mb-4">{{ f.icon }}</div>
          <h3 class="font-semibold mb-2">{{ f.title }}</h3>
          <p class="text-sm text-zinc-400 leading-relaxed">{{ f.desc }}</p>
        </div>
      </div>
    </section>

    <!-- Footer -->
    <footer class="border-t border-zinc-900 px-8 py-8 max-w-7xl mx-auto flex items-center justify-between">
      <div class="flex items-center gap-2">
        <div class="w-6 h-6 rounded-lg bg-indigo-600 flex items-center justify-center font-bold text-xs">M</div>
        <span class="text-sm text-zinc-500">Meridian</span>
      </div>
      <p class="text-xs text-zinc-600">&copy; {{ new Date().getFullYear() }} Meridian. Built with Laravel &amp; Vue.</p>
    </footer>
  </div>
</template>

<script lang="ts">
const features = [
  { icon: '◫', title: 'Issue Tracking', desc: 'Kanban boards, priority management, and custom workflows designed for modern engineering teams.' },
  { icon: '◈', title: 'Deep Analytics', desc: 'Velocity charts, cycle time analysis, and workload distribution across every project and sprint.' },
  { icon: '⚡', title: 'Smart Automation', desc: 'Trigger-based automation rules that keep your workflow moving without manual intervention.' },
  { icon: '🔔', title: 'Real-time Updates', desc: 'WebSocket-powered live updates so your team always sees the latest state of every issue.' },
  { icon: '🛡', title: 'RBAC & Permissions', desc: 'Owner, admin, member, and viewer roles with fine-grained access control per team.' },
  { icon: '🔗', title: 'Webhooks & API', desc: 'Connect Meridian to your existing tools via webhooks and a comprehensive REST API.' },
]
</script>
