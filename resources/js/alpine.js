import { remove, render } from './core.js'

export default function (Alpine) {
    Alpine.directive('turnstile', (element, { expression }, { evaluate, cleanup }) => {
        render(element, expression ? evaluate(expression) : {})

        cleanup(() => remove(element))
    })
}
