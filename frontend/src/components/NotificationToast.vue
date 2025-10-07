<template>
  <div class="fixed top-4 right-4 z-50 space-y-2">
    <div
      v-for="notification in notifications"
      :key="notification.id"
      :class="[
        'max-w-sm w-full bg-white shadow-lg rounded-lg pointer-events-auto ring-1 ring-black ring-opacity-5 overflow-hidden',
        getNotificationClass(notification.type)
      ]"
    >
      <div class="p-4">
        <div class="flex items-start">
          <div class="flex-shrink-0">
            <div :class="getIconClass(notification.type)" class="w-6 h-6">
              <svg fill="currentColor" viewBox="0 0 20 20">
                <path v-if="notification.type === 'success'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                <path v-else-if="notification.type === 'error'" fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                <path v-else-if="notification.type === 'warning'" fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                <path v-else fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
              </svg>
            </div>
          </div>
          <div class="ml-3 w-0 flex-1 pt-0.5">
            <p class="text-sm font-medium text-gray-900">{{ notification.title }}</p>
            <p v-if="notification.message" class="mt-1 text-sm text-gray-500">{{ notification.message }}</p>
          </div>
          <div class="ml-4 flex-shrink-0 flex">
            <button
              @click="removeNotification(notification.id)"
              class="bg-white rounded-md inline-flex text-gray-400 hover:text-gray-500 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
            >
              <span class="sr-only">Close</span>
              <svg class="h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
              </svg>
            </button>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue';

interface Notification {
  id: string;
  type: 'success' | 'error' | 'warning' | 'info';
  title: string;
  message?: string;
}

const notifications = ref<Notification[]>([]);

const getNotificationClass = (type: Notification['type']) => {
  const classes = {
    success: 'border-l-4 border-green-400',
    error: 'border-l-4 border-red-400',
    warning: 'border-l-4 border-yellow-400',
    info: 'border-l-4 border-blue-400'
  };
  return classes[type];
};

const getIconClass = (type: Notification['type']) => {
  const classes = {
    success: 'text-green-400',
    error: 'text-red-400',
    warning: 'text-yellow-400',
    info: 'text-blue-400'
  };
  return classes[type];
};

const removeNotification = (id: string) => {
  const index = notifications.value.findIndex(n => n.id === id);
  if (index > -1) {
    notifications.value.splice(index, 1);
  }
};

// Expose methods for global use
declare global {
  interface Window {
    showNotification: (notification: Omit<Notification, 'id'>) => void;
  }
}

window.showNotification = (notification: Omit<Notification, 'id'>) => {
  const id = Date.now().toString();
  notifications.value.push({ ...notification, id });
  
  // Auto remove after 5 seconds
  setTimeout(() => {
    removeNotification(id);
  }, 5000);
};
</script>