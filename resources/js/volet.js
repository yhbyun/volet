import { createApp } from 'vue'
import VoletFeedbackMessages from './components/features/VoletFeedbackMessages.vue'
import Volet from './components/Volet.vue'

// 네임스페이스된 객체로 래핑
const VoletApp = {
    app: null,

    async bootstrap() {
        if (this.app) {
            console.warn('Volet already initialized');
            return this.app;
        }

        try {
            this.app = createApp(Volet);
            this.app.component('VoletFeedbackMessages', VoletFeedbackMessages);

            // 안전한 전역 할당
            if (typeof window !== 'undefined') {
                window.VoletInstance = this.app;
            }

            this.app.mount('#volet');
            return this.app;
        } catch (error) {
            console.error('Volet bootstrap failed:', error);
            throw error;
        }
    },

    destroy() {
        if (this.app) {
            this.app.unmount();
            this.app = null;
            if (typeof window !== 'undefined') {
                delete window.VoletInstance;
            }
        }
    }
};

// 자동 초기화 (필요에 따라)
if (typeof window !== 'undefined' && document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        VoletApp.bootstrap();
    });
} else if (typeof window !== 'undefined') {
    VoletApp.bootstrap();
}

// IIFE 빌드를 위한 export (Vite가 자동으로 처리)
export default VoletApp;
