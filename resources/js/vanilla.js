import { remove, render } from './core.js'

const SELECTOR = '[data-jb-turnstile]'

function optionsFor(element) {
    const { sitekey, responseFieldName, theme, size, appearance } = element.dataset

    return { sitekey, responseFieldName, theme, size, appearance }
}

function eachWidget(node, callback) {
    if (!(node instanceof Element)) {
        return
    }

    if (node.matches(SELECTOR)) {
        callback(node)
    }

    node.querySelectorAll(SELECTOR).forEach(callback)
}

/**
 * Render every <x-turnstile::widget frontend="vanilla" /> on the page, including ones added later.
 * Do not use inside DOM controlled by Vue; use the Vue component there instead.
 */
export function init(root = document) {
    root.querySelectorAll(SELECTOR).forEach((element) => render(element, optionsFor(element)))

    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            mutation.addedNodes.forEach((node) => eachWidget(node, (element) => render(element, optionsFor(element))))
            mutation.removedNodes.forEach((node) => eachWidget(node, (element) => !element.isConnected && remove(element)))
        })
    })

    observer.observe(root === document ? document.body : root, { childList: true, subtree: true })

    return observer
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => init())
} else {
    init()
}
