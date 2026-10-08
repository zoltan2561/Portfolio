import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import vm from 'node:vm';

const source = readFileSync(new URL('../../public/script.js', import.meta.url), 'utf8');
const matrixSource = source.slice(source.indexOf('function setupMatrix()'), source.indexOf('function setupMenu()'));

function simulate(reduced = false) {
  let now = 100;
  let nextId = 0;
  const frames = new Map();
  const events = {};
  const draws = [];
  let glyphCount = 0;
  const motion = { matches: reduced, addEventListener: (_, fn) => { events.motion = fn; } };
  const context = { fillRect() {}, drawImage: (_, x, y) => draws.push({ x, y, now }) };
  const canvas = { hidden: false, getContext: () => context };
  const document = {
    readyState: 'loading', hidden: false, documentElement: { scrollHeight: 3000 },
    getElementById: () => canvas,
    createElement: () => { glyphCount++; return { getContext: () => ({ fillText() {} }) }; },
    addEventListener: (name, fn) => { events[name] = fn; }
  };
  const window = {
    innerWidth: 390, innerHeight: 844, scrollY: 0,
    matchMedia: () => motion,
    requestAnimationFrame: fn => { const id = ++nextId; frames.set(id, fn); return id; },
    cancelAnimationFrame: id => frames.delete(id),
    requestIdleCallback: fn => { events.idle = fn; },
    addEventListener: (name, fn) => { events[name] = fn; }
  };
  const deterministicMath = Object.create(Math);
  deterministicMath.random = () => 0.5;
  vm.runInNewContext(matrixSource + '\nsetupMatrix();', { document, window, performance: { now: () => now }, Math: deterministicMath });
  const tick = delta => {
    now += delta;
    const batch = [...frames.values()];
    frames.clear();
    batch.forEach(fn => fn(now));
  };
  return { canvas, document, window, motion, events, frames, draws, tick, glyphs: () => glyphCount };
}

function run(refreshRate) {
  const s = simulate();
  assert.equal(s.frames.size, 0, 'Rain waits for page load and idle time');
  s.events.load();
  s.events.idle();
  for (let i = 0; i < refreshRate; i++) s.tick(1000 / refreshRate);
  assert.equal(s.glyphs(), 4, 'Both original rain colours are cached once');
  return s.draws.filter(d => d.x === 0).at(-1).y;
}
assert.ok(Math.abs(run(60) - run(120)) <= 20, 'Rain movement is stable to one character row across refresh rates');

const s = simulate();
s.events.load();
s.events.idle();
assert.equal(s.frames.size, 1);
s.document.hidden = true;
s.events.visibilitychange();
assert.equal(s.frames.size, 0, 'Hidden tabs stop animation');
s.tick(30000);
s.document.hidden = false;
s.events.visibilitychange();
const before = s.draws.length;
s.tick(16);
assert.equal(s.draws.length, before, 'Resuming does not replay missed frames');
s.tick(34);
assert.ok(s.draws.length > before);
s.motion.matches = true;
s.events.motion();
assert.equal(s.canvas.hidden, true);
assert.equal(s.frames.size, 0, 'Reduced motion stops the loop');
s.motion.matches = false;
s.events.motion();
assert.equal(s.canvas.hidden, false);
assert.equal(s.frames.size, 1, 'Motion preference can be changed while the page is open');
s.window.innerWidth = 320;
s.events.resize();
s.events.resize();
s.tick(16);
assert.equal(s.canvas.width, 320, 'Resize is coalesced and updates the canvas');
const reduced = simulate(true);
reduced.events.load();
reduced.events.idle();
assert.equal(reduced.canvas.hidden, true);
assert.equal(reduced.frames.size, 0);
console.log('PASS Matrix: startup, 60/120 Hz timing, cached glyphs, background pause, reduced motion, resize');
