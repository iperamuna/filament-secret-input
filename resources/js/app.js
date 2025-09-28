import sodium from 'libsodium-wrappers';
(async () => {
    await sodium.ready;
    window.sodium = sodium; // <- actual instance
})();
