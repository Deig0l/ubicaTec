#!/usr/bin/env node
/**
 * Convierte los archivos legacy/piso{0,1,2}.js (JS con comentarios y
 * `var pisoNData = {...}`, NO JSON válido) a JSON plano en public/geo/pisoN.json.
 *
 * Uso: node scripts/convert-geojson.mjs
 */
import { readFileSync, writeFileSync, mkdirSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import path from 'node:path';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const rootDir = path.resolve(__dirname, '..');
const legacyDir = path.join(rootDir, 'legacy');
const outDir = path.join(rootDir, 'public', 'geo');

mkdirSync(outDir, { recursive: true });

const pisos = [0, 1, 2];

for (const n of pisos) {
    const varName = `piso${n}Data`;
    const srcPath = path.join(legacyDir, `piso${n}.js`);
    const outPath = path.join(outDir, `piso${n}.json`);

    const src = readFileSync(srcPath, 'utf8');

    // Evalúa el JS legacy (var pisoNData = {...};) y extrae el objeto.
    // eslint-disable-next-line no-new-func
    const fn = new Function(`${src}\n; return ${varName};`);
    const data = fn();

    if (!data || typeof data !== 'object') {
        throw new Error(`No se pudo evaluar ${varName} desde ${srcPath}`);
    }

    const json = JSON.stringify(data, null, 2);

    // Valida que el JSON generado sea válido antes de escribirlo.
    JSON.parse(json);

    writeFileSync(outPath, json, 'utf8');
    console.log(`OK: ${srcPath} -> ${outPath}`);
}

console.log('Conversión completa.');
