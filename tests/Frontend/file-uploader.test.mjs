import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';
import vm from 'node:vm';

function uploader() {
    const template = readFileSync(new URL('../../resources/views/components/file-uploader.blade.php', import.meta.url), 'utf8');
    let expression = template.split('x-data="')[1].split('"\n    :aria-busy')[0];
    expression = expression.replace(/@js\(/g, 'BLADE(');
    while (expression.includes('BLADE(')) {
        const start = expression.indexOf('BLADE(');
        let depth = 1, quote = null, end = start + 6;
        for (; depth; end++) {
            const char = expression[end];
            if (quote) { if (char === quote && expression[end - 1] !== '\\') quote = null; }
            else if (char === "'" || char === '"') quote = char;
            else if (char === '(') depth++;
            else if (char === ')') depth--;
        }
        expression = expression.slice(0, start) + "'value'" + expression.slice(end);
    }
    const state = vm.runInNewContext('(' + expression + ')', { URL, Promise });
    state.$refs = { input: { value: 'selected.png' } };
    state.file = { name: 'selected.png' };
    return state;
}

test('successful upload clears selection with the Livewire error bag API', async () => {
    const state = uploader();
    state.$wire = {
        upload(property, file, success) { success('temporary-file'); },
        async value() {},
        $errors: { any() { return false; } },
    };
    await state.upload();
    assert.equal(state.file, null, 'discard button must be hidden after success');
    assert.equal(state.$refs.input.value, '');
    assert.equal(state.error, '');
    assert.equal(state.busy, false);
});

test('successful removal keeps the wire reference when its triggering button is morphed away', async () => {
    const state = uploader();
    let detached = false;
    const refs = state.$refs;
    Object.defineProperty(state, '$refs', { get() { return detached ? {} : refs; } });
    const wire = { async value() { detached = true; }, $errors: { any() { return false; } } };
    Object.defineProperty(state, '$wire', { get() { return detached ? undefined : wire; } });
    await state.remove();
    assert.equal(state.error, '', 'a completed deletion must not report a failure');
    assert.equal(state.file, null);
    assert.equal(state.busy, false);
});

test('server validation failure retains selection for correction', async () => {
    const state = uploader();
    state.$wire = {
        upload(property, file, success) { success('temporary-file'); },
        async value() {},
        $errors: { any() { return true; } },
    };
    await state.upload();
    assert.equal(state.file.name, 'selected.png');
    assert.equal(state.status, '');
    assert.equal(state.busy, false);
});

test('failed removal shows an error and retains the selected file', async () => {
    const state = uploader();
    state.$wire = { async value() { throw new Error('Network failure'); } };
    await state.remove();
    assert.notEqual(state.error, '');
    assert.equal(state.file.name, 'selected.png');
    assert.equal(state.status, '');
    assert.equal(state.busy, false);
});
