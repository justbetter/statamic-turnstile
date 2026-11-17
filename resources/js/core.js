const SCRIPT_URL = 'https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit'

const WIDGET_SELECTOR = '[data-turnstile-widget-id]'

const widgets = new WeakMap()

let loader = null

export function loadTurnstile() {
    if (window.turnstile) {
        return Promise.resolve(window.turnstile)
    }

    if (loader) {
        return loader
    }

    loader = new Promise((resolve, reject) => {
        const script = document.createElement('script')

        script.src = SCRIPT_URL
        script.async = true
        script.onload = () => (window.turnstile ? resolve(window.turnstile) : reject(new Error('Turnstile did not initialise.')))
        script.onerror = () => {
            loader = null
            script.remove()
            reject(new Error('Failed to load Cloudflare Turnstile.'))
        }

        document.head.appendChild(script)
    })

    return loader
}

function dispatch(element, name, detail = {}) {
    element.dispatchEvent(new CustomEvent(`turnstile:${name}`, { bubbles: true, detail }))
}

function widgetElements(target) {
    if (!target) {
        return []
    }

    if (widgets.has(target)) {
        return [target]
    }

    return Array.from(target.querySelectorAll(WIDGET_SELECTOR)).filter((element) => widgets.has(element))
}

/**
 * Render a Turnstile widget into the element. Rendering the same element twice is a no-op.
 *
 * @param {HTMLElement} element
 * @param {{ sitekey: string, responseFieldName?: string, theme?: string, size?: string, appearance?: string, onVerified?: Function, onExpired?: Function, onError?: Function }} options
 */
export async function render(element, options = {}) {
    if (!element || widgets.has(element)) {
        return widgets.get(element)?.id ?? null
    }

    const state = { id: null, removed: false }
    widgets.set(element, state)

    let turnstile

    try {
        turnstile = await loadTurnstile()
    } catch (error) {
        widgets.delete(element)
        dispatch(element, 'error', { error })
        options.onError?.(error)

        return null
    }

    if (state.removed || !element.isConnected) {
        widgets.delete(element)

        return null
    }

    const config = {
        sitekey: options.sitekey,
        'response-field-name': options.responseFieldName ?? 'cf-turnstile-response',
        theme: options.theme,
        size: options.size,
        appearance: options.appearance,
        'refresh-expired': 'auto',
        callback: (token) => {
            dispatch(element, 'verified', { token })
            options.onVerified?.(token)
        },
        'expired-callback': () => {
            dispatch(element, 'expired')
            options.onExpired?.()
        },
        'error-callback': (code) => {
            dispatch(element, 'error', { code })
            options.onError?.(code)
        },
    }

    Object.keys(config).forEach((key) => config[key] === undefined && delete config[key])

    state.id = turnstile.render(element, config)
    element.setAttribute('data-turnstile-widget-id', state.id)

    return state.id
}

/**
 * Reset the widget(s) in a widget element or any ancestor such as the form, to get a fresh single-use token.
 */
export function reset(target) {
    widgetElements(target).forEach((element) => {
        const { id } = widgets.get(element)

        if (id !== null) {
            window.turnstile?.reset(id)
        }
    })
}

export function remove(target) {
    widgetElements(target).forEach((element) => {
        const state = widgets.get(element)

        state.removed = true

        if (state.id !== null) {
            window.turnstile?.remove(state.id)
        }

        element.removeAttribute('data-turnstile-widget-id')
        widgets.delete(element)
    })
}

export function getToken(target) {
    const [element] = widgetElements(target)
    const id = element ? widgets.get(element).id : null

    return id !== null ? (window.turnstile?.getResponse(id) ?? null) : null
}

if (typeof window !== 'undefined') {
    window.JustBetterTurnstile = { render, reset, remove, getToken }
}
