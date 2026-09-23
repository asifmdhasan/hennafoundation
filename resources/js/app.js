import './bootstrap';

const navigation = document.querySelector('#site-navigation');
const toggle = document.querySelector('.navigation-toggle');

if (navigation && toggle) {
    document.documentElement.classList.add('js');

    const closeNavigation = () => {
        navigation.classList.remove('is-open');
        toggle.setAttribute('aria-expanded', 'false');
    };

    toggle.addEventListener('click', () => {
        const isOpen = toggle.getAttribute('aria-expanded') === 'true';

        navigation.classList.toggle('is-open', !isOpen);
        toggle.setAttribute('aria-expanded', String(!isOpen));
    });

    navigation.addEventListener('click', (event) => {
        if (event.target instanceof HTMLAnchorElement) {
            closeNavigation();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && toggle.getAttribute('aria-expanded') === 'true') {
            closeNavigation();
            toggle.focus();
        }
    });

    window.matchMedia('(min-width: 901px)').addEventListener('change', closeNavigation);
}
