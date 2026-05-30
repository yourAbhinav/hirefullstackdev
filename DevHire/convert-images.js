const fs = require('fs');
const path = require('path');
const sharp = require('sharp');

const srcDir = path.join(__dirname, 'assets', 'images');
const outDir = path.join(srcDir, 'generated');
const manifestPath = path.join(outDir, 'images-manifest.json');

const widths = [320, 640, 1024, 1600];
const allowedExt = ['.jpg', '.jpeg', '.png', '.webp'];

(async () => {
  try {
    if (!fs.existsSync(srcDir)) {
      console.warn('No images directory found at', srcDir);
      process.exit(0);
    }

    if (!fs.existsSync(outDir)) {
      fs.mkdirSync(outDir, { recursive: true });
    }

    const files = fs.readdirSync(srcDir).filter(f => {
      const ext = path.extname(f).toLowerCase();
      return allowedExt.includes(ext) && fs.statSync(path.join(srcDir, f)).isFile();
    });

    const manifest = {};

    for (const file of files) {
      const inputPath = path.join(srcDir, file);
      const name = path.parse(file).name;

      const srcset = [];

      for (const w of widths) {
        const outName = `${name}-${w}.webp`;
        const outPath = path.join(outDir, outName);
        await sharp(inputPath)
          .resize({ width: w, withoutEnlargement: true })
          .webp({ quality: 80 })
          .toFile(outPath);
        srcset.push({ src: `assets/images/generated/${outName}`, width: w });
      }

      // Also generate a baseline webp (max size)
      const baselineOut = `${name}.webp`;
      await sharp(inputPath)
        .webp({ quality: 80 })
        .toFile(path.join(outDir, baselineOut));

      manifest[file] = {
        original: `assets/images/${file}`,
        webp: `assets/images/generated/${baselineOut}`,
        srcset
      };

      console.log('Processed', file);
    }

    fs.writeFileSync(manifestPath, JSON.stringify(manifest, null, 2));
    console.log('Image conversion complete. Manifest at', manifestPath);
  } catch (err) {
    console.error(err);
    process.exit(1);
  }
})();
