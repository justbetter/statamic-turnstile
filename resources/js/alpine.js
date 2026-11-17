import { remove, render } from './core.js'

/**
 * Alpine plugin adding the x-turnstile directive rendered by <x-turnstile::widget frontend="alpine" />.
 *
 * Usage: import turnstile from '.../resources/js/alpine.js'; Alpine.plugin(turnstile)
 */
export default function (Alpine) {
    Alpine.directive('turnstile', (element, { expression }, { evaluate, cleanup }) => {
        render(element, expression ? evaluate(expression) : {})

        cleanup(() => remove(element))
    })
}
