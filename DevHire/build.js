const esbuild = require('esbuild');
const path = require('path');

(async () => {
  try {
    // JS: bundle and minify main and navbar
    await esbuild.build({
      entryPoints: [path.join(__dirname, 'assets/js/main.js')],
      bundle: true,
      minify: true,
      sourcemap: false,
      target: ['es2017'],
      outfile: path.join(__dirname, 'assets/js/main.min.js')
    });

    await esbuild.build({
      entryPoints: [path.join(__dirname, 'assets/js/navbar.js')],
      bundle: true,
      minify: true,
      sourcemap: false,
      target: ['es2017'],
      outfile: path.join(__dirname, 'assets/js/navbar.min.js')
    });

    // CSS: minify main stylesheet to style.min.css
    await esbuild.build({
      entryPoints: [path.join(__dirname, 'assets/css/style.css')],
      bundle: true,
      minify: true,
      loader: { '.png': 'file', '.jpg': 'file', '.svg': 'file' },
      outfile: path.join(__dirname, 'assets/css/style.min.css')
    });

    console.log('Build complete.');
  } catch (err) {
    console.error(err);
    process.exit(1);
  }
})();
