window.cartoORG={
listeInter:[
  {
    "nom": "-- -- Fin Quai Arret CHN",
    "voie": "V1",
    "x": 14,
    "y": 247
  },
  {
    "nom": "Fin Quai Arret CHN -- Fin Quai arret KHA",
    "voie": "V1",
    "x": 20,
    "y": 291
  },
  {
    "nom": "Fin Quai arret KHA -- Fin Quai arret SAF",
    "voie": "V1",
    "x": 23,
    "y": 331
  },
  {
    "nom": "Fin Quai arret SAF -- Fin Quai arret 27 FEV",
    "voie": "V1",
    "x": 52,
    "y": 418
  },
  {
    "nom": "Fin Quai arret 27 FEV -- Fin Quai arret GRT",
    "voie": "V1",
    "x": 205,
    "y": 466
  },
  {
    "nom": "Fin Quai arret GRT -- Fin quai arret NPU",
    "voie": "V1",
    "x": 289,
    "y": 434
  },
  {
    "nom": "Fin quai arret NPU -- Fin Quai arret TEM",
    "voie": "V1",
    "x": 321,
    "y": 396
  },
  {
    "nom": "Fin Quai arret TEM -- Fin Quai arret EAM",
    "voie": "V1",
    "x": 432,
    "y": 394
  },
  {
    "nom": "Fin Quai arret EAM -- Fin quai arret CBA",
    "voie": "V1",
    "x": 492,
    "y": 395
  },
  {
    "nom": "Fin quai arret CBA -- Fin quai arret ECW",
    "voie": "V1",
    "x": 569,
    "y": 394
  },
  {
    "nom": "Fin quai arret ECW -- Fin Quai arret MKH",
    "voie": "V1",
    "x": 630,
    "y": 393
  },
  {
    "nom": "Fin Quai arret MKH -- Fin Quai arret HET",
    "voie": "V1",
    "x": 695,
    "y": 375
  },
  {
    "nom": "Fin Quai arret HET -- Fin quai arret ZOA",
    "voie": "V1",
    "x": 735,
    "y": 342
  },
  {
    "nom": "Fin quai arret ZOA -- fin quai arret BAH",
    "voie": "V1",
    "x": 775,
    "y": 302
  },
  {
    "nom": "fin quai arret BAH -- Fin quai arret COL",
    "voie": "V1",
    "x": 808,
    "y": 270
  },
  {
    "nom": "Fin quai arret COL -- Fin quai arret SID",
    "voie": "V1",
    "x": 815,
    "y": 230
  },
  {
    "nom": "Fin quai arret SID -- --",
    "voie": "V1",
    "x": 785,
    "y": 188
  },
  {
    "nom": "-- -- Fin quai arret SID",
    "voie": "V2",
    "x": 750,
    "y": 208
  },
  {
    "nom": "Fin quai arret SID -- Fin quai arret COL",
    "voie": "V2",
    "x": 782,
    "y": 237
  },
  {
    "nom": "Fin quai arret COL -- Fin quai arret BAH",
    "voie": "V2",
    "x": 773,
    "y": 255
  },
  {
    "nom": "Fin quai arret BAH -- Fin quai arret ZOA",
    "voie": "V2",
    "x": 746,
    "y": 283
  },
  {
    "nom": "Fin quai arret ZOA -- Fin quai arret HET",
    "voie": "V2",
    "x": 712,
    "y": 318
  },
  {
    "nom": "Fin quai arret HET -- Fin quai arret MKH",
    "voie": "V2",
    "x": 674,
    "y": 360
  },
  {
    "nom": "Fin quai arret MKH -- Fin quai arret ECW",
    "voie": "V2",
    "x": 630,
    "y": 368
  },
  {
    "nom": "Fin quai arret ECW -- Fin quai arret CBA",
    "voie": "V2",
    "x": 567,
    "y": 361
  },
  {
    "nom": "Fin quai arret CBA -- Fin quai arret EAM",
    "voie": "V2",
    "x": 498,
    "y": 362
  },
  {
    "nom": "Fin quai arret EAM -- Fin quai arret TEM",
    "voie": "V2",
    "x": 424,
    "y": 361
  },
  {
    "nom": "Fin quai arret TEM -- Fin quai arret NPU",
    "voie": "V2",
    "x": 320,
    "y": 363
  },
  {
    "nom": "Fin quai arret NPU -- Fin quai arret GRT",
    "voie": "V2",
    "x": 262,
    "y": 422
  },
  {
    "nom": "Fin quai arret GRT -- Fin quai Arret 27 FEV",
    "voie": "V2",
    "x": 203,
    "y": 422
  },
  {
    "nom": "Fin quai Arret 27 FEV -- Fin quai Arret SAF",
    "voie": "V2",
    "x": 104,
    "y": 414
  },
  {
    "nom": "Fin quai Arret SAF -- Fin quai Arret KHA",
    "voie": "V2",
    "x": 56,
    "y": 341
  },
  {
    "nom": "Fin quai Arret KHA -- Fin Quai Arret CHN",
    "voie": "V2",
    "x": 63,
    "y": 287
  },
  {
    "nom": "Fin Quai Arret CHN -- --",
    "voie": "V2",
    "x": 74,
    "y": 247
  }
],
initCarto: function(canvas, interstationsData) {
    const ctx = canvas.getContext('2d');
    const img = new Image();
    img.src = '/images/lignes/ligne-ORN.png';

    return new Promise((resolve, reject) => {
        img.onload = () => {
            this.drawCarte(ctx, img, interstationsData);
            resolve();
        };

        img.onerror = () => {
            console.error('Erreur de chargement de l\'image de la ligne ORN');
            reject();
        };
    });
},

drawCarte: function(ctx, img, interstationsData) {
    // Dessiner l'image de fond
    ctx.drawImage(img, 0, 0);

    // Dessiner les interstations
    this.listeInter.forEach((inter) => {
        const data = interstationsData.find(d => d.nom === inter.nom);
        if (data && data.total > 0) {
            // Taille proportionnelle au nombre d'excès
            const radius = Math.min(30, 10 + Math.sqrt(data.total) * 3);

            // Couleur selon la voie
            ctx.fillStyle = inter.voie === "V2" ?
                'rgba(0, 0, 255, 0.7)' : 'rgba(255, 0, 0, 0.7)';

            ctx.beginPath();
            ctx.arc(inter.x, inter.y, radius, 0, Math.PI * 2);
            ctx.fill();

            // Texte avec le nombre d'excès
            ctx.fillStyle = 'white';
            ctx.font = 'bold 12px Arial';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(data.total.toString(), inter.x, inter.y);

            // Nom de l'interstation
            ctx.fillStyle = '#333';
            ctx.font = '10px Arial';
            ctx.fillText(inter.nom, inter.x, inter.y + radius + 15);
        }
    });
},

getInterstationAtPosition: function(x, y, interstationsData, tolerance = 15) {
    for (const inter of this.listeInter) {
        const data = interstationsData.find(d => d.nom === inter.nom);
        if (data && data.total > 0) {
            const radius = Math.min(30, 10 + Math.sqrt(data.total) * 3);
            const distance = Math.sqrt(Math.pow(x - inter.x, 2) + Math.pow(y - inter.y, 2));

            if (distance <= radius + tolerance) {
                return { inter, data };
            }
        }
    }
    return null;
}
};
