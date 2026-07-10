<div
    style="
        position: relative;
        overflow: hidden;
        border-radius: 1.25rem;
        padding: 1.75rem 2rem;
        background: linear-gradient(120deg, #1e293b 0%, #0f172a 55%, #7c2d12 100%);
        color: #fff;
        box-shadow: 0 10px 30px -12px rgba(15, 23, 42, 0.55);
    "
>
    {{-- Adorno decorativo --}}
    <div style="position:absolute; top:-60px; right:-40px; width:220px; height:220px; border-radius:9999px; background:rgba(249,115,22,0.20); filter:blur(8px);"></div>
    <div style="position:absolute; bottom:-80px; right:120px; width:180px; height:180px; border-radius:9999px; background:rgba(249,115,22,0.12);"></div>

    <div style="position:relative; display:flex; flex-wrap:wrap; gap:1.5rem; align-items:center; justify-content:space-between;">
        <div>
            <p style="margin:0; font-size:0.8rem; letter-spacing:0.08em; text-transform:uppercase; color:#fdba74;">
                {{ $today }}
            </p>
            <h2 style="margin:0.35rem 0 0; font-size:1.7rem; font-weight:700; line-height:1.2;">
                {{ $greeting }}{{ $name ? ', ' . $name : '' }} 👋
            </h2>
            <p style="margin:0.4rem 0 0; color:#cbd5e1; font-size:0.95rem;">
                Aquí tienes el pulso de CercaDigital hoy. A por los mejores leads.
            </p>
        </div>

        <div style="display:flex; gap:0.9rem;">
            <div style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12); border-radius:0.9rem; padding:0.9rem 1.2rem; min-width:130px;">
                <p style="margin:0; font-size:0.75rem; color:#fdba74;">MRR</p>
                <p style="margin:0.2rem 0 0; font-size:1.5rem; font-weight:700;">€{{ number_format($mrr, 0, ',', '.') }}</p>
            </div>
            <div style="background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,0.12); border-radius:0.9rem; padding:0.9rem 1.2rem; min-width:130px;">
                <p style="margin:0; font-size:0.75rem; color:#fdba74;">Clientes activos</p>
                <p style="margin:0.2rem 0 0; font-size:1.5rem; font-weight:700;">{{ $activeClients }}</p>
            </div>
        </div>
    </div>
</div>
