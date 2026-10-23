// ======================= app.js (frontend) =======================
const API = '/api/teendok.php';
const CSRF = document.querySelector('meta[name="csrf"]').content;

// Egyetlen segédfüggvény minden API-híváshoz.
// FONTOS: a fetch csak hálózati hibánál dob kivételt, a 4xx/5xx státuszt nekünk kell ellenőrizni (res.ok).
async function api(metodus, url, adat) {
  const res = await fetch(url, {
    method: metodus,
    credentials: 'same-origin', // a session süti menjen vele
    headers: {'Content-Type': 'application/json', 'X-CSRF-Token': CSRF},
    body: adat === undefined ? undefined : JSON.stringify(adat),
  });
  if (res.status === 204) return null; // törlésnél nincs törzs
  const json = await res.json();
  if (!res.ok) throw new Error(json.hiba ?? 'HTTP ' + res.status);
  return json;
}

function hiba(e) {
  document.querySelector('#hiba').textContent = e.message;
}

// Lista lekérése és kirajzolása
async function betolt() {
  const lista = await api('GET', API);
  document.querySelector('#hiba').textContent = '';
  document.querySelector('#lista').replaceChildren(
    ...lista.map((t) => {
      const li = document.createElement('li');
      li.classList.toggle('kesz', t.kesz);

      const cb = document.createElement('input');
      cb.type = 'checkbox';
      cb.checked = t.kesz;
      cb.onchange = () =>
        api('PATCH', API + '?id=' + t.id, {kesz: cb.checked})
          .then(betolt)
          .catch(hiba);

      const span = document.createElement('span');
      span.textContent = t.cim; // textContent → XSS-biztos (nem innerHTML!)

      const del = document.createElement('button');
      del.textContent = 'Törlés';
      del.onclick = () =>
        api('DELETE', API + '?id=' + t.id)
          .then(betolt)
          .catch(hiba);

      li.append(cb, span, del);
      return li;
    }),
  );
}

// Új teendő küldése
document.querySelector('#uj').addEventListener('submit', async (ev) => {
  ev.preventDefault(); // ne töltse újra az oldalt
  try {
    await api('POST', API, {cim: ev.target.cim.value});
    ev.target.reset();
    await betolt();
  } catch (e) {
    hiba(e);
  }
});

betolt().catch(hiba);
