import { h } from 'vue'
import { getToken, remove, render, reset } from './core.js'

/**
 * Vue 3 component rendered by <x-turnstile::widget frontend="vue" />.
 *
 * Usage: import TurnstileWidget from '.../resources/js/vue.js'; app.component('turnstile-widget', TurnstileWidget)
 */
export default {
    name: 'TurnstileWidget',

    props: {
        sitekey: { type: String, required: true },
        responseFieldName: { type: String, default: 'cf-turnstile-response' },
        theme: { type: String, default: undefined },
        size: { type: String, default: undefined },
        appearance: { type: String, default: undefined },
    },

    emits: ['verified', 'expired', 'error'],

    expose: ['reset', 'getToken'],

    mounted() {
        render(this.$refs.container, {
            sitekey: this.sitekey,
            responseFieldName: this.responseFieldName,
            theme: this.theme,
            size: this.size,
            appearance: this.appearance,
            onVerified: (token) => this.$emit('verified', token),
            onExpired: () => this.$emit('expired'),
            onError: (error) => this.$emit('error', error),
        })
    },

    beforeUnmount() {
        remove(this.$refs.container)
    },

    methods: {
        reset() {
            reset(this.$refs.container)
        },

        getToken() {
            return getToken(this.$refs.container)
        },
    },

    // Turnstile injects its iframe and hidden input into this element; Vue never patches its children.
    render() {
        return h('div', { ref: 'container' })
    },
}
