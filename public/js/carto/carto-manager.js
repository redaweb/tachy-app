// public/js/carto/carto-manager.js
class CartoManager {
    constructor() {
        this.loadedCartos = {};
    }

    async loadCarto(site) {
        const siteLower = site.toLowerCase();
        const moduleName = `carto${site.toUpperCase()}`;

        if (this.loadedCartos[site]) {
            return this.loadedCartos[site];
        }

        try {
            await this.loadScript(`/js/carto/carto-${siteLower}.js`);
            this.loadedCartos[site] = window[moduleName];
            return this.loadedCartos[site];
        } catch (error) {
            console.error(`Erreur de chargement de la carto pour ${site}:`, error);
            return null;
        }
    }

    loadScript(src) {
        return new Promise((resolve, reject) => {
            if (document.querySelector(`script[src="${src}"]`)) {
                resolve();
                return;
            }

            const script = document.createElement('script');
            script.src = src;
            script.onload = resolve;
            script.onerror = reject;
            document.head.appendChild(script);
        });
    }

    getAvailableSites() {
        return ['ALG', 'ORN', 'CST', 'SBA', 'ORG', 'STF', 'MGM'];
    }

    hasCarto(site) {
        return this.getAvailableSites().includes(site.toUpperCase());
    }
}

// Instance globale
window.cartoManager = new CartoManager();
