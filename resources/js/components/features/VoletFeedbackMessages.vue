<template>
    <div class="volet-feedback" @paste="handlePaste">
        <!-- Category Selection -->
        <div v-if="!selectedCategory" class="volet-feedback-categories">
            <button
                v-for="category in categories"
                :key="category.slug"
                class="volet-feedback-category"
                @click="selectCategory(category)"
            >
                <img
                    v-if="category.icon"
                    :src="category.icon"
                    :alt="category.name"
                    class="volet-feedback-category-icon"
                />
                <span class="volet-feedback-category-name">{{ category.name }}</span>
            </button>
        </div>

        <!-- Feedback Form -->
        <form v-else class="volet-feedback-form" @submit.prevent="submitFeedback">
            <div class="volet-feedback-selected-category">
                <button type="button" @click="selectedCategory = null" class="volet-feedback-change-category">
                    <img
                        v-if="selectedCategory.icon"
                        :src="selectedCategory.icon"
                        :alt="selectedCategory.name"
                        class="volet-feedback-category-icon"
                    />
                    <span>{{ selectedCategory.name }}</span>
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                    </svg>
                </button>
            </div>

            <div class="volet-feedback-input">
                <label for="message" class="sr-only">Your feedback</label>
                <textarea
                    id="message"
                    v-model="message"
                    rows="4"
                    :placeholder="props.labels?.placeholder || `What's on your mind?`"
                    required
                    class="volet-feedback-textarea"
                ></textarea>
            </div>

            <!-- Screenshot Section -->
            <div class="volet-feedback-screenshot">
                <p v-if="screenshotError" class="volet-feedback-screenshot-error">
                    {{ screenshotError }}
                </p>
                <div class="volet-feedback-screenshot-actions">
                    <button type="button" @click="captureScreen" class="volet-feedback-screenshot-btn" :disabled="isCapturing">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="volet-screenshot-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0ZM18.75 10.5h.008v.008h-.008V10.5Z" />
                        </svg>
                        <span v-if="isCapturing">{{ props.labels?.['capturing'] || 'Capturing...' }}</span>
                        <span v-else>{{ props.labels?.['capture-screen'] || 'Capture Screen' }}</span>
                    </button>
                    <input
                        ref="fileInput"
                        type="file"
                        accept="image/*"
                        @change="handleFileUpload"
                        class="volet-screenshot-file-input"
                        style="display: none"
                    />
                    <button type="button" @click="$refs.fileInput.click()" class="volet-feedback-screenshot-btn">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="volet-screenshot-icon">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18.75 19.5H6.75Z" />
                        </svg>
                        <span>{{ props.labels?.['upload-image'] || 'Upload Image' }}</span>
                    </button>
                </div>

                <!-- Screenshot Preview -->
                <div v-if="screenshots.length > 0" class="volet-feedback-screenshots">
                    <div v-for="(screenshot, index) in screenshots" :key="index" class="volet-feedback-screenshot-item">
                        <img :src="screenshot.preview" :alt="`Screenshot ${index + 1}`" class="volet-feedback-screenshot-preview" />
                        <button type="button" @click="removeScreenshot(index)" class="volet-feedback-screenshot-remove">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>

                <p v-if="screenshots.length === 0" class="volet-feedback-screenshot-hint">
                    {{ props.labels?.['screenshot-hint'] || 'You can also paste screenshots with Ctrl+V' }}
                </p>
            </div>

            <div class="volet-feedback-actions">
                <p v-if="error" class="volet-feedback-error">
                    {{ error }}
                </p>
                <button
                    type="submit"
                    class="volet-button"
                    :disabled="isSubmitting"
                >
                    <span v-if="isSubmitting">{{ props.labels?.['button-loading'] || 'Sending...' }}</span>
                    <span v-else>{{ props.labels?.button || 'Send feedback' }}</span>
                </button>
            </div>
        </form>

        <!-- Success Message -->
        <div v-if="showSuccess" class="volet-feedback-success">
            <IconResolver :icon="content['success-icon']" class="volet-feedback-success-icon" />
            <h4>{{ props.labels?.['success-title'] || 'Thank you for your feedback!' }}</h4>
            <p>{{ props.labels?.['success-subtitle'] || 'We appreciate your input and will review it shortly.' }}</p>
            <button @click="reset" class="volet-feedback-reset">{{ props.labels?.['send-another'] || 'Send another feedback' }}</button>
        </div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import IconResolver from "../IconResolver.vue";

// Props from feature config
const props = defineProps({
    categories: {
        type: Array,
        required: true
    },
    routes: {
        type: Object,
        required: true
    },
    labels: {
        type: Object,
        required: true
    },
    content: {
        type: Object,
        required: true
    },
    csrfToken: {
        type: String,
        required: true
    }
})

defineEmits(['close'])

const selectedCategory = ref(null)
const message = ref('')
const isSubmitting = ref(false)
const showSuccess = ref(false)
const error = ref('')
const screenshots = ref([])
const isCapturing = ref(false)
const screenshotError = ref('')

const selectCategory = (category) => {
    selectedCategory.value = category
    error.value = '' // Clear any previous errors
}

// Screenshot handling functions
const captureScreen = async () => {
    if (!navigator.mediaDevices || !navigator.mediaDevices.getDisplayMedia) {
        screenshotError.value = props.labels?.['error-capture-not-supported'] || 'Screen capture is not supported in this browser'
        return
    }

    isCapturing.value = true
    try {
        const stream = await navigator.mediaDevices.getDisplayMedia({
            video: { mediaSource: 'screen' }
        })

        const video = document.createElement('video')
        video.srcObject = stream
        video.play()

        video.addEventListener('loadedmetadata', () => {
            const canvas = document.createElement('canvas')
            canvas.width = video.videoWidth
            canvas.height = video.videoHeight
            const ctx = canvas.getContext('2d')
            ctx.drawImage(video, 0, 0)

            canvas.toBlob((blob) => {
                addScreenshot(blob)
                stream.getTracks().forEach(track => track.stop())
            }, 'image/png')
        })
    } catch (err) {
        if (err.name !== 'NotAllowedError') {
            screenshotError.value = props.labels?.['error-capture-failure'] || 'Failed to capture screen. Please try again.'
        }
    } finally {
        isCapturing.value = false
    }
}

const handleFileUpload = (event) => {
    const file = event.target.files[0]
    if (file && file.type.startsWith('image/')) {
        addScreenshot(file)
    }
    event.target.value = '' // Reset input
}

const handlePaste = (event) => {
    const items = event.clipboardData?.items
    if (!items) return

    for (const item of items) {
        if (item.type.startsWith('image/')) {
            event.preventDefault()
            const file = item.getAsFile()
            if (file) {
                addScreenshot(file)
            }
            break
        }
    }
}

const addScreenshot = (file) => {
    if (screenshots.value.length >= 4) { // Limit to 4 screenshots
        screenshotError.value = props.labels?.['max-screenshots'] || '4 screenshots allowed'
        return
    }

    const reader = new FileReader()
    reader.onload = (e) => {
        screenshots.value.push({
            file: file,
            preview: e.target.result,
            data: e.target.result // Base64 data for sending
        })
    }
    reader.readAsDataURL(file)
}

const removeScreenshot = (index) => {
    screenshots.value.splice(index, 1)
}

const submitFeedback = async () => {
    if (isSubmitting.value) return

    error.value = '' // Clear any previous errors
    isSubmitting.value = true

    try {
        const payload = {
            category: selectedCategory.value.slug,
            message: message.value,
            user_info: {
                viewportWidth: window.innerWidth,
                viewportHeight: window.innerHeight
            }
        }

        // Add screenshots if any
        if (screenshots.value.length > 0) {
            payload.screenshots = screenshots.value.map(screenshot => ({
                data: screenshot.data,
                filename: `screenshot_${Date.now()}.png`,
                type: 'image/png'
            }))
        }

        const response = await fetch(`${props.routes.store}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': props.csrfToken
            },
            body: JSON.stringify(payload)
        })

        if (!response.ok) {
            const data = await response.json()
            throw new Error(data.message || props.labels?.error || 'Failed to submit feedback')
        }

        showSuccess.value = true
    } catch (err) {
        error.value = err.message || props.labels?.error || 'Failed to submit feedback. Please try again.'
        console.error('Failed to submit feedback:', err)
    } finally {
        isSubmitting.value = false
    }
}

const reset = () => {
    selectedCategory.value = null
    message.value = ''
    showSuccess.value = false
    error.value = ''
    screenshots.value = []
}
</script>
