// -> Versionsnummer aus der eigenen URL (?ver=…) auch an importierte Module weitergeben,
//    damit der Browser nach einem Upload nie eine alte Version aus dem Cache nimmt.
const version = new URL(import.meta.url).searchParams.get('ver') || '';
const withVersion = (path) => (version ? `${path}?ver=${encodeURIComponent(version)}` : path);

const wheels = document.querySelectorAll('.work-wheel');
if (wheels.length) {
    import(withVersion('./work-wheel.js'))
        .then(({ initWorkWheel }) => wheels.forEach(initWorkWheel))
        .catch((error) => console.error('[work-wheel] konnte nicht geladen werden:', error));
}
