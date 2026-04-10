/* Central stylesheet generated from original inline styles */
/* Source: distribuicao_importar_base.php */
.base-import-shell{max-width:1200px;margin:0 auto;}
.base-import-card{background:linear-gradient(135deg,rgba(15,23,42,.96),rgba(17,24,39,.92));border:1px solid rgba(148,163,184,.18);border-radius:22px;box-shadow:0 20px 60px rgba(0,0,0,.28);overflow:hidden}
.base-import-head{display:flex;justify-content:space-between;align-items:flex-start;gap:20px;padding:28px 30px 10px}
.base-import-head h3{margin:0;font-size:28px;line-height:1.15}
.base-import-head p{margin:8px 0 0;color:#94a3b8;max-width:760px}
.base-import-body{padding:22px 30px 30px}
.base-import-grid{display:grid;grid-template-columns:1.1fr 1fr;gap:18px;margin-bottom:18px}
.base-field{display:flex;flex-direction:column;gap:8px}
.base-field label{font-size:13px;font-weight:700;color:#cbd5e1;letter-spacing:.02em}
.base-field select,.base-filebox{height:54px;border-radius:14px;border:1px solid rgba(148,163,184,.22);background:rgba(2,6,23,.55);color:#fff;padding:0 16px;outline:none;transition:.2s}
.base-field select:focus,.base-filebox:focus-within{border-color:rgba(239,68,68,.6);box-shadow:0 0 0 4px rgba(239,68,68,.12)}
.base-filebox{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:10px 12px 10px 16px}
.base-filebox strong{font-size:12px;color:#94a3b8;display:block;margin-bottom:2px}
.base-filebox span{font-size:14px;color:#fff}
.base-filebox input[type=file]{display:none}
.base-filebtn{display:inline-flex;align-items:center;justify-content:center;height:36px;padding:0 14px;border-radius:10px;border:1px solid rgba(148,163,184,.25);background:rgba(30,41,59,.75);color:#fff;font-weight:700;cursor:pointer;white-space:nowrap}
.base-help{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin:6px 0 22px}
.base-tip{border:1px solid rgba(148,163,184,.14);background:rgba(15,23,42,.52);border-radius:16px;padding:14px 16px}
.base-tip small{display:block;color:#94a3b8;margin-bottom:6px;text-transform:uppercase;letter-spacing:.08em;font-size:11px}
.base-tip strong{font-size:15px;color:#fff}
.base-actions{display:flex;justify-content:flex-end;gap:12px}
.base-actions .btn{min-width:180px;height:48px;border-radius:14px}
@media (max-width: 900px){.base-import-head,.base-import-body{padding-left:18px;padding-right:18px}.base-import-grid,.base-help{grid-template-columns:1fr}.base-actions{justify-content:stretch}.base-actions .btn{width:100%}}


/* Source: distribuicao_index.php */
.content{max-width:none!important}.content-wrap{padding:22px 22px 34px!important}

.dist-filters{display:grid;grid-template-columns:2.1fr repeat(7,minmax(120px,1fr)) auto;gap:12px;align-items:end;padding:18px;background:linear-gradient(180deg,rgba(10,16,28,.96),rgba(13,20,35,.92));border-bottom:1px solid rgba(255,255,255,.06)}
.dist-filters .fg{display:flex;flex-direction:column;gap:6px;min-width:0}.dist-filters .fg.wide{grid-column:auto}
.dist-filters .fg label{font-size:10px;color:#8fa1bf;text-transform:uppercase;letter-spacing:.12em;font-weight:800}
.dist-filters input,.dist-filters select{width:100%;height:42px;padding:10px 12px;font-size:13px;border-radius:12px;border:1px solid rgba(255,255,255,.08);background:rgba(255,255,255,.03);box-shadow:inset 0 1px 0 rgba(255,255,255,.02)}
.dist-filters input:focus,.dist-filters select:focus{outline:none;border-color:rgba(163,29,30,.5);box-shadow:0 0 0 3px rgba(163,29,30,.12)}
.dist-filters .factions{display:flex;gap:8px;align-self:end}.dist-filters .factions .btn{height:42px;padding-inline:16px}

.dtbl{width:100%;border-collapse:collapse;table-layout:auto;font-size:12.5px}.dtbl thead th{background:#181d26;padding:10px 12px;text-align:left;font-size:10.5px;color:var(--muted2);text-transform:uppercase;letter-spacing:.08em;border-bottom:2px solid var(--line);white-space:nowrap;font-weight:700}.dtbl thead th:first-child{border-left:3px solid transparent}.dtbl tbody tr{border-bottom:1px solid var(--line);transition:background .1s}.dtbl tbody tr:hover{background:rgba(255,255,255,.025)}.dtbl td{padding:9px 12px;vertical-align:middle;color:var(--text)}.dtbl td:first-child{border-left:3px solid transparent}
.dtbl tbody tr.row-offline td:first-child{border-left-color:#ff6b6b}.dtbl tbody tr.row-offline{background:rgba(255,107,107,.03)}.dtbl tbody tr.row-troca td:first-child{border-left-color:#f59e0b}.dtbl tbody tr.row-troca{background:rgba(245,158,11,.03)}.dtbl tbody tr.row-desinstalada td:first-child{border-left-color:#94a3b8}.dtbl tbody tr.row-desinstalada{background:rgba(148,163,184,.03)}
.dp{font-weight:700;font-size:13px;line-height:1.25}.ds{color:var(--muted);font-size:11.5px;margin-top:2px;line-height:1.4}.dm{font-family:var(--font-mono);font-size:11.5px;color:#b0bdd6}.dlb{color:var(--muted2);font-size:10.5px;margin-right:2px}
.dbt-edit{display:inline-flex;align-items:center;gap:5px;padding:5px 10px;border-radius:9px;font-size:12px;font-weight:700;background:rgba(96,165,250,.12);color:#b8d9ff;border:1px solid rgba(96,165,250,.2);text-decoration:none;transition:all .13s;white-space:nowrap}.dbt-edit svg{width:13px;height:13px}.dbt-edit:hover{background:rgba(96,165,250,.22);border-color:rgba(96,165,250,.38);color:#d6eaff}
.dbt-icon{display:inline-flex;align-items:center;justify-content:center;width:30px;height:30px;border-radius:8px;border:1px solid var(--line2);background:#1b2029;color:var(--muted);text-decoration:none;transition:all .12s;flex-shrink:0}.dbt-icon svg{width:13px;height:13px}.dbt-icon:hover{color:var(--text);background:#202634;border-color:#465064}.dbt-icon.swap:hover{background:rgba(245,158,11,.1);border-color:rgba(245,158,11,.3);color:#ffd083}.dbt-icon.hist:hover{background:rgba(96,165,250,.1);border-color:rgba(96,165,250,.3);color:#b8d9ff}.d-actions{display:flex;align-items:center;gap:6px}.mon-last{font-size:10.5px;color:var(--muted2);margin-top:3px}
.company-highlight{font-size:14px;color:#fff;font-weight:800}
.overview-card{background:linear-gradient(135deg,rgba(13,20,35,.98),rgba(16,24,41,.94));border:1px solid rgba(255,255,255,.06);border-radius:22px;padding:18px 18px 12px;box-shadow:var(--shadow);margin-bottom:16px}
.overview-grid{display:grid;grid-template-columns:1.2fr .95fr;gap:16px;align-items:center}.overview-meta{display:grid;grid-template-columns:repeat(2,minmax(150px,1fr));gap:10px}.metric-chip{padding:12px 14px;border-radius:16px;border:1px solid rgba(255,255,255,.06);background:rgba(255,255,255,.03)}.metric-chip .label{font-size:10px;text-transform:uppercase;letter-spacing:.12em;color:#8fa1bf;font-weight:800}.metric-chip .value{font-size:24px;font-weight:800;color:#fff;line-height:1.15;margin-top:4px}.metric-chip .sub{font-size:11px;color:#95a4bc;margin-top:3px}
.analytics-grid{display:grid;grid-template-columns:1.05fr 1.25fr 1fr;gap:14px;margin-bottom:18px}.analytics-card{background:linear-gradient(180deg,#171c24 0%,#161b23 100%);border:1px solid var(--line);border-radius:20px;padding:18px;box-shadow:var(--shadow)}.analytics-card .head{display:flex;align-items:center;justify-content:space-between;gap:10px;margin-bottom:14px}.analytics-card .head h3{font-size:15px;letter-spacing:-.02em}.analytics-card .head span{font-size:11px;color:var(--muted2);text-transform:uppercase;letter-spacing:.08em;font-weight:700}
.city-rank{display:flex;flex-direction:column;gap:12px}.city-row{display:grid;grid-template-columns:28px 1fr auto;gap:12px;align-items:center}.city-row .rank{width:28px;height:28px;border-radius:999px;background:linear-gradient(180deg,rgba(163,29,30,.3),rgba(163,29,30,.14));border:1px solid rgba(163,29,30,.34);display:flex;align-items:center;justify-content:center;font-size:11px;font-weight:800;color:#fff}.city-row .meta{display:flex;flex-direction:column;gap:6px}.city-row .name{font-size:13px;font-weight:700;color:#eef2f7}.city-row .bar{height:8px;border-radius:999px;background:rgba(255,255,255,.06);overflow:hidden}.city-row .fill{height:100%;border-radius:999px;background:linear-gradient(90deg,rgba(163,29,30,.72),rgba(255,124,124,.94))}.city-row .count{font-family:var(--font-mono);font-size:12px;color:#d4dded;padding:5px 10px;border-radius:999px;background:rgba(255,255,255,.04);border:1px solid rgba(255,255,255,.05)}
.chart-wrap{position:relative;min-height:260px}.chart-wrap.short{min-height:220px}.chart-wrap.tall{min-height:280px}.chart-legend-inline{display:flex;gap:8px;flex-wrap:wrap;margin-top:10px}.chart-legend-inline .badge{padding:7px 11px;border-radius:999px}
@media (max-width:1320px){.dist-filters{grid-template-columns:repeat(4,minmax(150px,1fr))}.dist-filters .fg.wide{grid-column:1 / -2}.dist-filters .factions{grid-column:-2 / -1}}
@media (max-width:1080px){.overview-grid,.analytics-grid{grid-template-columns:1fr}.overview-meta{grid-template-columns:repeat(2,minmax(120px,1fr))}.chart-wrap{min-height:240px}.dist-filters{grid-template-columns:1fr 1fr}.dist-filters .fg.wide,.dist-filters .factions{grid-column:1 / -1}}
@media (max-width:720px){.overview-meta{grid-template-columns:1fr}.dist-filters{grid-template-columns:1fr}}


/* Source: faturas.php */
.toolbar-select{width:100%;background:#12171f;border:1px solid #313a47;color:#eef2f8;border-radius:12px;padding:9px 34px 9px 12px;font-size:13.5px;outline:none;transition:border-color .12s, box-shadow .12s, background .12s;min-height:40px}
.toolbar-select:focus{border-color:rgba(163,29,30,.54);box-shadow:0 0 0 3px rgba(163,29,30,.12);background:#151b24}
.faturas-hero{display:grid;grid-template-columns:minmax(0,1.5fr) minmax(280px,.7fr);gap:16px;margin-bottom:18px}
.faturas-chart-card{padding:18px 18px 14px}
.faturas-chart-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;flex-wrap:wrap;margin-bottom:18px}
.faturas-chart-head h3{margin:0;font-size:18px;letter-spacing:-.02em;display:flex;align-items:center;gap:8px;line-height:1.2}
.faturas-chart-head h3 svg{width:18px;height:18px;flex:0 0 18px;display:block}
.faturas-chart-head p{margin:6px 0 0;color:var(--muted2);font-size:13px}
.faturas-total-box{display:flex;flex-direction:column;align-items:flex-end;gap:4px;text-align:right}
.faturas-total-box .label{font-size:11px;color:var(--muted2);font-family:var(--font-mono);text-transform:uppercase;letter-spacing:.08em}
.faturas-total-box .value{font-size:26px;font-weight:800;letter-spacing:-.05em;color:var(--text)}
.faturas-chart{display:grid;grid-template-columns:repeat(12,minmax(0,1fr));gap:10px;align-items:end;min-height:240px}
.faturas-bar-col{display:flex;flex-direction:column;align-items:center;gap:8px;text-decoration:none}
.faturas-bar-wrap{position:relative;width:100%;height:170px;display:flex;align-items:flex-end;justify-content:center;padding:0 4px}
.faturas-bar{width:min(34px,100%);border-radius:12px 12px 4px 4px;background:linear-gradient(180deg,#bf2122 0%,#7b1617 100%);box-shadow:0 10px 24px rgba(163,29,30,.16);transition:transform .12s ease, filter .12s ease, opacity .12s ease;min-height:10px}
.faturas-bar-col:hover .faturas-bar{transform:translateY(-2px);filter:brightness(1.06)}
.faturas-bar-col.is-muted .faturas-bar{opacity:.3;box-shadow:none}
.faturas-bar-col.is-active .faturas-bar{background:linear-gradient(180deg,#ff6464 0%,#a31d1e 100%);box-shadow:0 14px 30px rgba(163,29,30,.28)}
.faturas-bar-label{font-size:11px;font-family:var(--font-mono);color:var(--muted2);text-transform:uppercase;letter-spacing:.06em}
.faturas-bar-value{font-size:11px;color:var(--text);font-weight:700}
.faturas-side-card{padding:18px;display:flex;flex-direction:column;gap:12px}
.faturas-side-top{display:flex;justify-content:space-between;align-items:flex-start;gap:12px}
.faturas-side-title{margin:0;font-size:17px}
.faturas-side-copy{margin:4px 0 0;color:var(--muted2);font-size:13px;line-height:1.45}
.faturas-kpis{display:grid;grid-template-columns:1fr;gap:10px;margin-top:4px}
.faturas-kpi{background:#161b23;border:1px solid var(--line);border-radius:16px;padding:13px 14px}
.faturas-kpi .k{font-size:11px;color:var(--muted2);font-family:var(--font-mono);text-transform:uppercase;letter-spacing:.08em}
.faturas-kpi .v{margin-top:5px;font-size:20px;font-weight:800;color:var(--text);letter-spacing:-.04em}
.faturas-kpi .s{margin-top:4px;font-size:12px;color:var(--muted2)}
@media (max-width:980px){.faturas-hero{grid-template-columns:1fr}.faturas-chart{gap:8px}.faturas-total-box{align-items:flex-start;text-align:left}}
@media (max-width:640px){.faturas-chart{grid-template-columns:repeat(6,minmax(0,1fr))}.faturas-bar-wrap{height:120px}}


/* Source: impressao_financeiro.php */
:root{
    --fin-sidebar-width: 260px;
}

.page-shell.fin-page{
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 12px 16px 16px 16px !important;
    box-sizing: border-box !important;
}

.page-shell.fin-page .container,
.page-shell.fin-page .container-fluid{
    width: 100% !important;
    max-width: 100% !important;
    margin: 0 !important;
    padding: 0 !important;
}
}
.fin-page{display:flex;flex-direction:column;gap:22px}
.fin-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start}
.fin-hero-copy h2{margin:0 0 8px}
.fin-hero-copy p{margin:0;color:#91a0b5;max-width:1180px;line-height:1.6}
.fin-card,.fin-filters-card{border-radius:20px}
.fin-filters{display:grid;grid-template-columns:minmax(180px,1.15fr) minmax(140px,.8fr) minmax(160px,.95fr) minmax(180px,1fr) minmax(130px,.72fr) auto;gap:12px;align-items:end}
.fin-filters .form-group{display:flex;flex-direction:column;gap:7px}
.fin-filters label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#98a5bb;font-weight:700}
.fin-filters select,.fin-filters input{
    width:100%;background:#12171f;border:1px solid #313a47;color:#eef2f8;border-radius:13px;
    padding:12px 14px;font-size:13.5px;outline:none;transition:border-color .12s,box-shadow .12s,background .12s;min-height:46px
}
.fin-filters select:focus,.fin-filters input:focus{border-color:rgba(163,29,30,.54);box-shadow:0 0 0 3px rgba(163,29,30,.12);background:#151b24}
.fin-filter-actions{display:flex;gap:10px;flex-wrap:nowrap;justify-content:flex-end;align-items:center}
.fin-note{margin-top:18px;padding:14px 16px;border:1px dashed rgba(163,29,30,.30);border-radius:15px;background:rgba(163,29,30,.06);color:#d8dfe8;line-height:1.6}
.fin-tabs{display:flex;gap:12px;flex-wrap:wrap;align-items:center}
.fin-tab{
    display:inline-flex;align-items:center;justify-content:center;gap:8px;padding:12px 18px;border-radius:14px;
    border:1px solid rgba(255,255,255,.08);background:linear-gradient(180deg,#1a2230 0%, #151b24 100%);
    color:#d8e1ed;text-decoration:none;font-size:13px;font-weight:800;white-space:nowrap;
    box-shadow:0 8px 22px rgba(0,0,0,.18), inset 0 1px 0 rgba(255,255,255,.03);
    transition:all .2s ease
}
.fin-tab:hover{transform:translateY(-1px);color:#fff}
.fin-tab.active{
    background:linear-gradient(180deg,rgba(163,29,30,.95) 0%, rgba(122,22,23,.95) 100%);
    color:#fff;border-color:rgba(255,140,140,.32)
}
.fin-kpis{display:grid;grid-template-columns:repeat(5,minmax(0,1fr));gap:18px;margin-bottom:20px;align-items:stretch}
.fin-kpi{position:relative;overflow:hidden;display:flex;flex-direction:column;justify-content:flex-start;min-height:124px;background:linear-gradient(180deg,#171d26 0%,#121821 100%);border:1px solid rgba(124,142,167,.22);border-radius:20px;padding:20px 20px 18px;box-shadow:0 18px 34px rgba(0,0,0,.18)}
.fin-kpi:before{content:"";position:absolute;left:0;right:0;top:0;height:4px;background:linear-gradient(90deg,rgba(163,29,30,.95),rgba(240,86,88,.55));opacity:.95}
.fin-kpi .lbl{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#9ca9bc;font-weight:800}
.fin-kpi .val{margin-top:12px;font-size:19px;font-weight:900;letter-spacing:-.03em;color:#f5f8fb;line-height:1.2;word-break:break-word}
.fin-kpi .sub{margin-top:9px;font-size:12px;color:#8d99ae;line-height:1.45}
.fin-kpi-total .val,.fin-kpi-aluguel .val{font-size:17px}
.fin-kpi-total:before{background:linear-gradient(90deg,#b11f21,#ff6b6d)}
.fin-kpi-aluguel:before{background:linear-gradient(90deg,#3856ff,#5fa2ff)}
.fin-kpi-paginas:before{background:linear-gradient(90deg,#0ea5e9,#4ade80)}
.fin-kpi-impressoras:before{background:linear-gradient(90deg,#7c3aed,#a78bfa)}
.fin-kpi-empresas:before{background:linear-gradient(90deg,#f59e0b,#fbbf24)}
@media (max-width: 1400px){.fin-kpis{grid-template-columns:repeat(3,minmax(220px,1fr));}}
@media (max-width: 980px){.fin-kpis{grid-template-columns:repeat(2,minmax(220px,1fr));}}
@media (max-width: 640px){.fin-kpis{grid-template-columns:1fr;}}
.fin-grid-2{display:grid;grid-template-columns:1.4fr 1fr;gap:24px}
.fin-card-head{display:flex;justify-content:space-between;gap:14px;align-items:flex-start;margin-bottom:16px}
.fin-card-head h3{margin:0 0 5px}
.fin-card-head p{margin:0;color:#91a0b5;line-height:1.5}
.fin-badge{display:inline-flex;align-items:center;gap:6px;border:1px solid rgba(163,29,30,.28);background:rgba(163,29,30,.10);color:#ffd7d7;padding:8px 11px;border-radius:999px;font-size:12px;font-weight:700;white-space:nowrap}
.fin-table-wrap{
    overflow-x: auto;
    overflow-y: hidden;
    width: 100%;
    max-width: 100%;
    border: 1px solid var(--line);
    border-radius: 16px;
}
.fin-table{
    width: 100%;
    min-width: 100%;
    border-collapse: separate;
    border-spacing: 0;
    table-layout: auto;
}

.fin-table.table-pivot{
    width: max-content;
    min-width: 2200px;
}

.fin-table.table-wide{
    width: max-content;
    min-width: 3200px;
}

.fin-table.table-resumo,
.fin-table.table-compact,
.fin-table.table-import{
    width: 100%;
    min-width: 100%;
}.fin-table th,.fin-table td{
    padding: 14px 18px;
    border-bottom: 1px solid var(--line);
    font-size: 13px;
    vertical-align: top;
}

.fin-table.table-pivot th,.fin-table.table-pivot td,
.fin-table.table-wide th,.fin-table.table-wide td{
    white-space: nowrap;
}
.fin-table th{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:#8f9bb0;background:#151a21;position:sticky;top:0;z-index:1;white-space:nowrap}
.fin-table tbody tr:hover td{background:rgba(255,255,255,.015)}
.fin-table td strong{display:block;color:#f4f7fb;font-size:13px;line-height:1.45}
.fin-table tr:last-child td{border-bottom:none}
.fin-table .muted{display:block;color:#8d99ae;font-size:12px;line-height:1.45;margin-top:2px}
.fin-chart-box{height:420px;position:relative}
.fin-month-list{display:flex;flex-wrap:wrap;gap:10px;margin-bottom:16px}
.fin-month-chip{display:inline-flex;align-items:center;gap:8px;padding:10px 13px;border-radius:999px;border:1px solid var(--line);background:#151a21;color:#d8e0ea;font-size:12px}
.fin-empty{padding:32px 20px;color:#91a0b5}
.fin-sticky-first th:first-child,.fin-sticky-first td:first-child{position:sticky;left:0;background:#151a21;z-index:2}
.fin-sticky-first tbody td:first-child{background:#121822}
.fin-sticky-two th:nth-child(2),.fin-sticky-two td:nth-child(2){position:sticky;left:220px;background:#151a21;z-index:2}
.fin-sticky-two tbody td:nth-child(2){background:#121822}
@media (max-width:1500px){
    .fin-filters{grid-template-columns:1fr 1fr}
    .fin-filter-actions{grid-column:1 / -1}
    .fin-grid-2{grid-template-columns:1fr}
}
@media (max-width:900px){
    .page-shell.fin-page{
        width:calc(100vw - 16px) !important;
        margin-left:calc(50% - 50vw + 8px) !important;
        margin-right:calc(50% - 50vw + 8px) !important;
    }
    .fin-hero{flex-direction:column}
    .fin-filters,.fin-kpis{grid-template-columns:1fr}
    .fin-kpis{grid-template-columns:repeat(2,minmax(0,1fr))}
    .fin-tab{flex:0 1 auto;padding:10px 14px;font-size:12px}
}
@media (max-width:680px){
    .fin-kpis{grid-template-columns:1fr}
    .fin-chart-box{height:320px}
    .fin-tab{flex:1 1 calc(50% - 6px);padding:10px 12px;font-size:11px}
}

.fin-card,
.fin-filters-card,
.fin-kpis,
.fin-grid-2,
.fin-table-wrap{
    width: 100% !important;
    max-width: none !important;
}

.fin-grid-2{
    grid-template-columns: 1fr !important;
    gap: 18px !important;
}

.fin-table.table-pivot th:first-child,
.fin-table.table-pivot td:first-child{
    position: sticky;
    left: 0;
    z-index: 2;
    min-width: 320px;
    background: #121822;
}

.fin-table.table-pivot thead th:first-child{
    background: #151a21;
    z-index: 3;
}

.fin-table.table-pivot th:not(:first-child),
.fin-table.table-pivot td:not(:first-child){
    min-width: 170px;
    text-align: right;
    white-space: nowrap;
}

.fin-table.table-resumo th:first-child,
.fin-table.table-resumo td:first-child{
    width: 58%;
}

.fin-table.table-resumo th:not(:first-child),
.fin-table.table-resumo td:not(:first-child){
    width: 14%;
}


/* Source: impressao_financeiro_importar.php */
.import-card{max-width:860px;margin:0 auto}
.import-card .note{font-size:13px;color:#95a1b6;line-height:1.55}
.import-card input[type=file], .import-card select{width:100%;background:#12171f;border:1px solid #313a47;color:#eef2f8;border-radius:12px;padding:12px;font-size:13.5px;min-height:46px}
.import-list{display:flex;flex-direction:column;gap:10px;margin-top:14px}
.import-tip{padding:14px 16px;border-radius:14px;background:#161c25;border:1px solid var(--line);color:#90a0b6;font-size:13px}


/* Source: index.php */
.dashboard-hero{display:flex;justify-content:space-between;gap:18px;align-items:flex-start;padding:22px 24px;background:linear-gradient(135deg,rgba(163,29,30,.16),rgba(163,29,30,.05) 50%,rgba(255,255,255,.02));border:1px solid rgba(163,29,30,.22);border-radius:22px;box-shadow:var(--shadow);margin-bottom:18px;overflow:hidden;position:relative}
.dashboard-hero::after{content:"";position:absolute;right:-70px;top:-60px;width:220px;height:220px;border-radius:50%;background:radial-gradient(circle,rgba(163,29,30,.18),transparent 70%)}
.hero-copy{position:relative;z-index:1;max-width:720px}
.hero-kicker{display:inline-flex;align-items:center;gap:8px;padding:6px 10px;border-radius:999px;background:rgba(255,255,255,.05);border:1px solid rgba(255,255,255,.07);color:#f5d6d6;font-size:11px;font-weight:800;text-transform:uppercase;letter-spacing:.08em;margin-bottom:12px}
.hero-title{font-size:30px;line-height:1.02;letter-spacing:-.05em;margin-bottom:10px}
.hero-subtitle{font-size:14px;color:var(--muted);max-width:680px}
.hero-quick{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:10px;min-width:290px;position:relative;z-index:1}
.hero-chip{padding:14px;border-radius:16px;background:rgba(18,22,29,.72);border:1px solid var(--line2)}
.hero-chip strong{display:block;font-size:22px;line-height:1;letter-spacing:-.04em;margin-bottom:6px}
.hero-chip span{font-size:12px;color:var(--muted)}
.kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px;margin-bottom:18px}
.kpi-card{position:relative;overflow:hidden;text-decoration:none;color:inherit;background:linear-gradient(180deg,#171b22 0%,#161a21 100%);border:1px solid var(--line);border-radius:18px;padding:18px 18px 16px;box-shadow:var(--shadow);transition:transform .14s ease,border-color .14s ease}
.kpi-card:hover{transform:translateY(-2px);border-color:#40495c}
.kpi-head{display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:14px}
.kpi-label{font-size:11px;text-transform:uppercase;letter-spacing:.08em;color:var(--muted2);font-weight:800}
.kpi-icon{width:42px;height:42px;border-radius:14px;background:#1d2330;border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;color:#d9e1ef}
.kpi-icon svg{width:18px;height:18px}
.kpi-value{font-size:30px;font-weight:800;line-height:1;letter-spacing:-.05em;margin-bottom:8px}
.kpi-sub{display:flex;gap:12px;flex-wrap:wrap;font-size:12px;color:var(--muted)}
.kpi-sub .ok{color:#98ecb7}.kpi-sub .warn{color:#ffcf84}.kpi-sub .soft{color:#cfd7e5}
.kpi-strip{position:absolute;left:0;right:0;bottom:0;height:3px;background:linear-gradient(90deg,var(--accent),transparent 84%)}
.dashboard-panels{display:grid;grid-template-columns:minmax(0,1.35fr) minmax(280px,.85fr);gap:16px;margin-bottom:16px}
.dashboard-panels-2{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px;margin-bottom:16px}
.panel-header{display:flex;align-items:flex-start;justify-content:space-between;gap:14px;margin-bottom:16px}
.panel-title{display:flex;align-items:center;gap:10px;font-size:16px;font-weight:800;letter-spacing:-.02em}
.panel-title svg{width:17px;height:17px}
.panel-note{font-size:12px;color:var(--muted2);margin-top:4px}
.mini-chart{display:flex;align-items:flex-end;gap:10px;height:180px;padding-top:10px}
.mini-chart-col{flex:1;display:flex;flex-direction:column;align-items:center;justify-content:flex-end;gap:8px;min-width:0}
.mini-chart-barwrap{width:100%;height:110px;display:flex;align-items:flex-end}
.mini-chart-bar{width:100%;border-radius:10px 10px 4px 4px;background:linear-gradient(180deg,#cb4748 0%,#8f191a 100%);box-shadow:0 10px 18px rgba(163,29,30,.18)}
.mini-chart-label{font-size:11.5px;color:var(--muted)}
.mini-chart-value{font-size:11px;color:#d8dfea}
.list-metric,.setor-list{display:flex;flex-direction:column;gap:12px}
.list-row{display:flex;flex-direction:column;gap:6px}
.list-top{display:flex;align-items:center;justify-content:space-between;gap:10px}
.list-name{font-size:13px;font-weight:700;color:var(--text);min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.list-num{font-size:12px;color:#dbe3f0}
.progress{height:8px;border-radius:999px;background:#212633;overflow:hidden;border:1px solid rgba(255,255,255,.03)}
.progress > span{display:block;height:100%;border-radius:999px;background:linear-gradient(90deg,var(--accent),#d14949)}
.recent-card{padding:0;overflow:hidden}
.recent-head{display:flex;align-items:center;justify-content:space-between;gap:14px;padding:16px 18px;border-bottom:1px solid var(--line)}
.recent-actions{display:flex;gap:8px;flex-wrap:wrap}
@media (max-width: 1080px){.hero-quick{grid-template-columns:repeat(3,1fr);min-width:0;width:100%}.dashboard-hero{flex-direction:column}.kpi-grid{grid-template-columns:repeat(2,1fr)}.dashboard-panels,.dashboard-panels-2{grid-template-columns:1fr}}
@media (max-width: 640px){.hero-title{font-size:24px}.hero-quick,.kpi-grid{grid-template-columns:1fr}.mini-chart{gap:7px;height:150px}.mini-chart-barwrap{height:88px}.recent-head{flex-direction:column;align-items:flex-start}}


/* Source: login.php */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
:root {
    --accent:#a31d1e; --accent-h:#bf2122;
    --bg:#0d0d0d; --bg2:#141414; --bg3:#1c1c1c;
    --bdr:#232323; --bdr2:#2e2e2e;
    --t1:#f2f2f2; --t2:#888; --t3:#444; --r:8px;
}
html { font-size:16px; }
body {
    font-family:'Inter',sans-serif; background:var(--bg); color:var(--t1);
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    padding:20px; -webkit-font-smoothing:antialiased;
}
body::before {
    content:''; position:fixed; inset:0;
    background-image: linear-gradient(var(--bdr) 1px,transparent 1px), linear-gradient(90deg,var(--bdr) 1px,transparent 1px);
    background-size:48px 48px; opacity:.3; pointer-events:none;
}
.box {
    background:var(--bg2); border:1px solid var(--bdr); border-radius:16px;
    padding:40px 36px; width:100%; max-width:400px; position:relative; z-index:1;
    box-shadow:0 24px 64px rgba(0,0,0,.5);
}
.logo-area { margin-bottom:28px; display:flex; flex-direction:column; align-items:center; gap:8px; }
.logo-area img { width:180px; height:auto; }
.logo-area .fb { display:flex; align-items:center; gap:10px; }
.logo-area .fb .lm { width:40px;height:40px;background:var(--accent);border-radius:10px;display:flex;align-items:center;justify-content:center;font-size:20px; }
.logo-area .fb .lt { font-size:20px;font-weight:800;letter-spacing:-.4px; }
.logo-area .sub { font-size:11.5px;color:var(--t3);letter-spacing:1px;text-transform:uppercase; }
.fg { display:flex;flex-direction:column;gap:6px;margin-bottom:15px; }
label { font-size:12.5px;font-weight:600;color:var(--t2); }
input { background:var(--bg3);border:1px solid var(--bdr2);color:var(--t1);padding:11px 14px;border-radius:var(--r);font-size:15px;font-family:'Inter',sans-serif;width:100%;outline:none;transition:border-color .12s,box-shadow .12s; }
input:focus { border-color:var(--accent);box-shadow:0 0 0 3px rgba(163,29,30,.12); background:#202020; }
.iw { position:relative; }
.iw input { padding-right:44px; }
.eye { position:absolute;right:12px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--t3);padding:2px;display:flex;transition:color .12s; }
.eye:hover { color:var(--t2); }
.eye svg { width:16px;height:16px; }
.btn-in { width:100%;background:var(--accent);color:#fff;border:none;cursor:pointer;padding:12px;border-radius:var(--r);font-size:15px;font-weight:700;font-family:'Inter',sans-serif;margin-top:8px;transition:background .12s,transform .12s; }
.btn-in:hover { background:var(--accent-h);transform:translateY(-1px); }
.err { background:rgba(163,29,30,.08);border:1px solid rgba(163,29,30,.2);color:#f87171;padding:11px 14px;border-radius:var(--r);font-size:13.5px;margin-bottom:16px;line-height:1.5; }
.info { background:rgba(34,197,94,.07);border:1px solid rgba(34,197,94,.18);color:#4ade80;padding:11px 14px;border-radius:var(--r);font-size:13.5px;margin-bottom:16px; }
.ft { margin-top:22px;text-align:center;font-size:11px;color:var(--t3);letter-spacing:.3px; }


/* Source: perfil.php */
.profile-grid{display:grid;grid-template-columns:340px minmax(0,1fr);gap:18px;align-items:start}
.profile-stack{display:flex;flex-direction:column;gap:18px}
.profile-hero{display:flex;gap:14px;align-items:flex-start}
.profile-avatar{width:58px;height:58px;border-radius:16px;background:linear-gradient(135deg,var(--accent),#c93435);display:flex;align-items:center;justify-content:center;font-weight:800;font-size:22px;color:#fff;flex-shrink:0}
.profile-name{font-size:20px;font-weight:800;letter-spacing:-.3px}
.profile-sub{font-size:13px;color:var(--t2);margin-top:3px}
.profile-chips{display:flex;gap:8px;flex-wrap:wrap;margin-top:12px}
.profile-chip{display:inline-flex;align-items:center;gap:6px;padding:6px 10px;border-radius:999px;background:var(--bg3);border:1px solid var(--bdr2);font-size:12px;color:var(--t2)}
.profile-details{display:grid;grid-template-columns:1fr;gap:12px;margin-top:16px}
.profile-item{padding:11px 12px;border-radius:12px;background:var(--bg3);border:1px solid var(--bdr)}
.profile-label{font-size:10.5px;color:var(--t3);text-transform:uppercase;letter-spacing:.8px;margin-bottom:4px;font-family:'JetBrains Mono',monospace}
.profile-value{font-size:14px;font-weight:600;color:var(--t1)}
.modules-list{display:grid;gap:8px}
.module-row{display:flex;align-items:center;justify-content:space-between;gap:12px;padding:10px 12px;border-radius:12px;background:var(--bg3);border:1px solid var(--bdr)}
.module-row .left{font-size:13.5px;font-weight:500}
.module-on{color:var(--clr-green);display:inline-flex;align-items:center;gap:5px;font-size:12px;font-weight:600}
.module-off{color:var(--t3);font-size:12px}
@media (max-width:900px){.profile-grid{grid-template-columns:1fr}}


/* Source: includes/header.php */
*,*::before,*::after{box-sizing:border-box;margin:0;padding:0}
:root{
  --accent:#a31d1e;
  --accent-hover:#bd2425;
  --accent-soft:rgba(163,29,30,.12);
  --bg:#0f1115;
  --bg2:#171a21;
  --bg3:#1d212b;
  --bg4:#252a36;
  --bg5:#303746;
  --line:#252a36;
  --line2:#323848;
  --text:#eef2f7;
  --muted:#a7b0c2;
  --muted2:#6d7688;
  --green:#22c55e;
  --amber:#f59e0b;
  --blue:#60a5fa;
  --red:#f87171;
  --radius:12px;
  --radius-lg:18px;
  --sidebar:244px;
  --shadow:0 16px 36px rgba(0,0,0,.28);
  --font: "Segoe UI", Inter, Roboto, Arial, sans-serif;
  --font-mono: Consolas, "Courier New", monospace;
}
html{font-size:16px;-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale;text-rendering:optimizeLegibility}
body{font-family:var(--font);background:linear-gradient(180deg,#0d0f13 0%,#12161d 100%);color:var(--text);min-height:100vh;display:flex;line-height:1.45}
a{color:inherit}
svg{display:block;max-width:100%;height:auto}
.sidebar{width:var(--sidebar);min-height:100vh;background:linear-gradient(180deg,#151922 0%,#12161d 100%);border-right:1px solid var(--line);display:flex;flex-direction:column;position:fixed;inset:0 auto 0 0;z-index:200;transition:transform .22s ease}
.sidebar-logo{padding:6px 10px 6px;border-bottom:1px solid var(--line);display:flex;justify-content:center;align-items:center;min-height:58px}
.sidebar-logo img{display:block;width:100%;max-width:228px;height:auto;object-fit:contain;filter:drop-shadow(0 8px 18px rgba(0,0,0,.16))}
.logo-fallback{display:flex;align-items:center;gap:12px}
.logo-mark{width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,var(--accent),#c63738);display:flex;align-items:center;justify-content:center;color:#fff;box-shadow:0 8px 18px rgba(163,29,30,.22)}
.logo-mark svg{width:18px;height:18px}
.logo-text strong{display:block;font-size:16px;letter-spacing:-.3px}
.logo-text span{display:block;margin-top:2px;font-size:11px;color:var(--muted2)}
.sidebar-scroll{padding:8px 10px;flex:1;overflow-y:auto}
.nav-section{margin-bottom:12px}
.nav-label{padding:0 8px 6px;font-size:10px;font-weight:700;color:var(--muted2);text-transform:uppercase;letter-spacing:.1em}
.nav-stack{display:flex;flex-direction:column;gap:6px}
.nav-link{display:flex;align-items:center;gap:10px;padding:9px 10px;border-radius:12px;color:var(--muted);text-decoration:none;border:1px solid transparent;transition:all .14s ease;font-size:13px;font-weight:600;letter-spacing:.01em}
.nav-link:hover{background:rgba(255,255,255,.03);border-color:var(--line2);color:var(--text);transform:translateX(1px)}
.nav-link.active{background:linear-gradient(180deg,rgba(163,29,30,.18),rgba(163,29,30,.08));border-color:rgba(163,29,30,.34);color:#fff;box-shadow:inset 0 1px 0 rgba(255,255,255,.03)}
.nav-link .ni{width:30px;height:30px;border-radius:10px;background:#1f2430;border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;flex:0 0 30px;color:#c7cfdd}
.nav-link.active .ni{background:rgba(163,29,30,.16);border-color:rgba(163,29,30,.34);color:#fff}
.nav-link .ni svg{width:14px;height:14px}
.nav-meta{display:flex;flex-direction:column;gap:1px;min-width:0}
.nav-meta strong{font-size:13px;font-weight:700;line-height:1.15;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.nav-meta span{font-size:10.5px;color:var(--muted2);font-weight:500}
.sidebar-footer{padding:10px 10px 12px;border-top:1px solid var(--line);display:flex;flex-direction:column;gap:8px}
.sidebar-user-card{display:flex;align-items:center;gap:10px;text-decoration:none;padding:10px;border-radius:14px;background:#1b2029;border:1px solid var(--line2);transition:all .14s ease}
.sidebar-user-card:hover{background:#202633;border-color:#40495c}
.sidebar-user-avatar{width:36px;height:36px;border-radius:12px;background:linear-gradient(135deg,var(--accent),#ca3a3b);display:flex;align-items:center;justify-content:center;flex:0 0 36px;color:#fff;font-size:13px;font-weight:800;box-shadow:0 8px 16px rgba(163,29,30,.16)}
.sidebar-user-name{font-size:12.5px;font-weight:700;color:var(--text);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.sidebar-user-role{font-size:10.5px;color:var(--muted2);margin-top:1px}
.sidebar-logout-link{display:flex;align-items:center;justify-content:center;gap:8px;padding:9px 10px;border-radius:12px;background:transparent;border:1px solid var(--line);text-decoration:none;color:#ef6b6d;font-size:12.5px;font-weight:700;transition:all .14s ease}
.sidebar-logout-link:hover{background:rgba(248,113,113,.08);border-color:rgba(248,113,113,.22);color:#ffc0c0}
.sidebar-logout-link .ni{width:16px;height:16px;display:inline-flex;align-items:center;justify-content:center;flex:0 0 16px}
.sidebar-logout-link .ni svg{width:16px!important;height:16px!important}
.sidebar-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,.58);backdrop-filter:blur(2px);z-index:150}
.main{margin-left:var(--sidebar);flex:1;display:flex;flex-direction:column;min-width:0}
.topbar{height:68px;position:sticky;top:0;z-index:100;background:rgba(15,17,21,.92);backdrop-filter:blur(12px);border-bottom:1px solid var(--line)}
.topbar-inner{max-width:1220px;margin:0 auto;height:100%;padding:0 28px;display:flex;align-items:center;justify-content:space-between;gap:16px}
.menu-toggle{display:none;background:#1d212b;border:1px solid var(--line2);color:var(--muted);width:40px;height:40px;border-radius:12px;cursor:pointer}
.menu-toggle svg{width:18px;height:18px;margin:0 auto}
.topbar-title{display:flex;align-items:center;gap:12px;min-width:0}
.topbar-title-icon{width:36px;height:36px;border-radius:12px;background:#1b2029;border:1px solid var(--line2);display:flex;align-items:center;justify-content:center;color:#dbe2ee;flex:0 0 36px}
.topbar-title-icon svg{width:16px;height:16px}
.topbar-title-text strong{display:block;font-size:18px;letter-spacing:-.03em;line-height:1.1}
.topbar-title-text span{display:block;margin-top:2px;font-size:12px;color:var(--muted2)}
.topbar-actions{display:flex;align-items:center;gap:8px}
.content-wrap{flex:1;display:flex;justify-content:center;padding:24px 28px 30px}
.content{width:100%;max-width:1220px}
.btn{display:inline-flex;align-items:center;gap:8px;padding:10px 14px;border-radius:12px;font-size:13px;font-weight:700;text-decoration:none;border:none;cursor:pointer;transition:all .14s ease;font-family:var(--font);line-height:1;white-space:nowrap}
.btn svg{width:15px;height:15px}
.btn-primary{background:var(--accent);color:#fff;box-shadow:0 10px 18px rgba(163,29,30,.18)}
.btn-primary:hover{background:var(--accent-hover);transform:translateY(-1px)}
.btn-ghost{background:#1b2029;color:var(--muted);border:1px solid var(--line2)}
.btn-ghost:hover{background:#222938;color:var(--text);border-color:#434b5d}
.btn-danger{background:rgba(248,113,113,.09);color:#ffb4b4;border:1px solid rgba(248,113,113,.16)}
.btn-danger:hover{background:rgba(248,113,113,.16)}
.btn-edit{background:rgba(96,165,250,.10);color:#b8d9ff;border:1px solid rgba(96,165,250,.16)}
.btn-sm{padding:8px 12px;font-size:12.5px}
.btn-xs{padding:6px 10px;font-size:12px}
.card{background:linear-gradient(180deg,#171b22 0%,#161a21 100%);border:1px solid var(--line);border-radius:18px;padding:20px 22px;box-shadow:var(--shadow)}
.table-wrap{overflow:auto;border-radius:14px}
table{width:100%;border-collapse:collapse;table-layout:auto}
thead th{background:#1b2029;padding:12px 14px;text-align:left;font-size:11px;color:var(--muted2);text-transform:uppercase;letter-spacing:.08em;border-bottom:1px solid var(--line);white-space:nowrap;font-weight:700}
tbody tr{border-bottom:1px solid var(--line);transition:background .12s}
tbody tr:hover{background:rgba(255,255,255,.02)}
td{padding:13px 14px;font-size:13.5px;color:var(--text);vertical-align:middle}
.badge{display:inline-flex;align-items:center;gap:6px;padding:4px 9px;border-radius:999px;font-size:11.5px;font-weight:700;border:1px solid transparent;line-height:1.1}
.badge svg{width:13px;height:13px}
.b-green{background:rgba(34,197,94,.12);color:#8ef0af;border-color:rgba(34,197,94,.18)}
.b-amber{background:rgba(245,158,11,.12);color:#ffd083;border-color:rgba(245,158,11,.18)}
.b-blue{background:rgba(96,165,250,.12);color:#bddbff;border-color:rgba(96,165,250,.18)}
.b-red{background:rgba(248,113,113,.12);color:#ffb8b8;border-color:rgba(248,113,113,.18)}
.b-gray,.b-neutral{background:rgba(148,163,184,.10);color:#d0d8e5;border-color:rgba(148,163,184,.18)}
.alert{display:flex;align-items:center;gap:10px;padding:14px 16px;border-radius:14px;margin-bottom:16px;border:1px solid var(--line2);font-weight:700}
.alert svg{width:16px;height:16px}
.alert-success{background:rgba(34,197,94,.10);color:#9cebba;border-color:rgba(34,197,94,.18)}
.alert-error{background:rgba(248,113,113,.10);color:#ffb2b2;border-color:rgba(248,113,113,.18)}
.stitle{display:flex;align-items:center;gap:10px;font-size:15px;font-weight:800;letter-spacing:-.02em;margin-bottom:16px}
.stitle svg{width:17px;height:17px;color:#d8dfeb}
.sub{font-size:12px;color:var(--muted2);margin-top:2px;line-height:1.35}
.device-tag,.setor-tag{display:inline-flex;align-items:center;padding:4px 8px;border-radius:999px;background:#1b2029;border:1px solid var(--line2);font-size:11.5px;color:#d3dbeb}
.mono{font-family:'Cascadia Mono','JetBrains Mono','Consolas','Courier New',monospace;font-size:12px}
.btn-icon{display:inline-flex;align-items:center;justify-content:center;width:32px;height:32px;border-radius:10px;border:1px solid var(--line2);background:#1b2029;color:var(--muted);text-decoration:none;transition:all .12s}
.btn-icon svg{width:14px;height:14px}
.btn-icon:hover{color:var(--text);background:#202634;border-color:#465064}
.btn-icon.del{color:#ef5a5d;border-color:rgba(239,90,93,.22);background:rgba(239,90,93,.08)}.btn-icon.del:hover{background:rgba(239,90,93,.14);border-color:rgba(239,90,93,.34);color:#ff8f91}.btn-icon.del svg{width:14px;height:14px}
.empty-state{display:flex;flex-direction:column;align-items:center;justify-content:center;text-align:center;padding:34px 18px;color:var(--muted2)}
.empty-state svg{width:30px;height:30px;margin-bottom:10px;opacity:.7}
.empty-state p{font-size:13px;line-height:1.5}
.empty-state a{color:#fff}
.page-head{display:flex;align-items:flex-end;justify-content:space-between;gap:18px;margin-bottom:20px;flex-wrap:wrap}
.page-head-copy h2{font-size:28px;line-height:1.05;letter-spacing:-.04em;margin-bottom:8px}
.page-head-copy p{color:var(--muted);font-size:14px;max-width:720px}
.page-head-actions{display:flex;gap:10px;flex-wrap:wrap}
.dashboard-grid{display:grid;grid-template-columns:repeat(12,1fr);gap:16px}
@media (max-width: 1100px){.sidebar{transform:translateX(-100%)}.sidebar.open{transform:translateX(0)}.sidebar-overlay.open{display:block}.main{margin-left:0}.menu-toggle{display:inline-flex;align-items:center;justify-content:center}.topbar-inner,.content-wrap{padding-left:18px;padding-right:18px}.sidebar-logo img{max-width:168px}}
@media (max-width: 820px){.topbar-title-text span{display:none}.content-wrap{padding:18px 14px 22px}.card{padding:16px}.page-head-copy h2{font-size:22px}.btn-sm{padding:8px 10px}}
@media (max-width: 640px){.topbar-actions{display:none}.sidebar{width:min(86vw,300px)}.sidebar-logo{min-height:84px}.nav-link{padding:10px}.nav-link .ni{width:32px;height:32px}}

/* ===== Standardized UI layer ===== */
:root{--line:#2a303b;--line2:#353d4a;--panel:#171c24;--panel2:#1b212b;--soft:#202733;--shadow:0 14px 34px rgba(0,0,0,.22);--font-mono:'Cascadia Mono','JetBrains Mono','Consolas','Courier New',monospace}
html,body{font-family:'Segoe UI Variable Text','Segoe UI','Inter',system-ui,-apple-system,BlinkMacSystemFont,'Helvetica Neue',Arial,sans-serif;text-rendering:geometricPrecision}
body{-webkit-font-smoothing:auto;-moz-osx-font-smoothing:auto}
.sidebar{width:262px;background:linear-gradient(180deg,#171b22 0%,#151922 100%)}
.sidebar-logo{padding:20px 18px 18px}
.sidebar-logo img{max-width:182px;margin:0 auto}
.sidebar-scroll{padding:14px 12px 12px;display:flex;flex-direction:column;gap:14px;overflow-y:auto;flex:1}
.nav-section{display:flex;flex-direction:column;gap:8px}
.nav-label{padding:0 8px;font-size:10px;font-weight:800;letter-spacing:.16em;text-transform:uppercase;color:#687181}
.nav-stack{display:flex;flex-direction:column;gap:6px}
.nav-link{border:1px solid transparent;border-radius:16px;padding:12px 12px 12px 14px;margin:0;gap:12px;border-left:none;background:transparent}
.nav-link:hover{background:#1a202a;border-color:#2f3744;color:#f2f5fb}
.nav-link.active{background:linear-gradient(180deg,rgba(163,29,30,.14),rgba(163,29,30,.08));border-color:rgba(163,29,30,.28);box-shadow:inset 0 1px 0 rgba(255,255,255,.02)}
.nav-link .ni{width:18px;height:18px;opacity:.88}
.nav-link .ni svg{width:18px;height:18px}
.nav-meta{display:flex;flex-direction:column;min-width:0;gap:2px}
.nav-meta strong{font-size:13.5px;font-weight:700;letter-spacing:-.01em;color:inherit}
.nav-meta span{font-size:11.5px;color:var(--t3);white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.nav-link.active .nav-meta span{color:#aeb8cb}
.sidebar-bottom{padding:14px 12px 14px;margin-top:auto;border-top:1px solid var(--line)}
.sidebar-user-card{padding:13px 12px;border-radius:16px;background:#1a2029;border:1px solid var(--line2)}
.sidebar-user-avatar{border-radius:12px}
.sidebar-logout-link{margin-top:10px;justify-content:flex-start;padding:11px 12px;border-radius:14px}
.topbar{height:68px;background:rgba(23,27,34,.92);backdrop-filter:blur(10px)}
.topbar-inner{max-width:1240px}
.topbar-title{font-size:15px}
.topbar-title-text strong{display:block;font-size:16px;line-height:1.1;letter-spacing:-.02em}
.topbar-title-text span{font-size:12px;color:#7f8897}
.content-wrap{padding:24px 26px 28px}
.content{max-width:1240px}
.card{background:linear-gradient(180deg,#171c24 0%,#161b23 100%);border:1px solid var(--line);border-radius:20px;box-shadow:var(--shadow)}
.stitle{font-size:15px;font-weight:800;margin-bottom:14px}
.stitle svg{width:16px;height:16px}
.btn{border-radius:12px;font-size:13px;padding:10px 14px;gap:8px}
.btn-sm{padding:9px 12px;font-size:12.5px}
.btn-primary{background:linear-gradient(180deg,#b52223 0%,#9f1d1e 100%);color:#fff;box-shadow:0 10px 24px rgba(163,29,30,.22)}
.btn-ghost{background:#1a202a;border:1px solid var(--line2);color:#d4dcec}
.btn-ghost:hover{background:#202733;border-color:#455066;color:#fff}
.table-wrap{border-radius:16px}
thead th{font-size:10.5px;color:#7f8998;background:#1a202a;padding:13px 14px}
td{padding:14px}
.toolbar-shell{display:grid;grid-template-columns:minmax(260px,1.6fr) 220px 220px auto;gap:10px;padding:16px 18px 14px;border-bottom:1px solid var(--line);background:rgba(255,255,255,.01)}
.toolbar-grow input,.toolbar-field input,.toolbar-field select,.toolbar-field textarea,.form-group input,.form-group select,.form-group textarea{width:100%;background:#12171f;border:1px solid #313a47;color:#eef2f8;border-radius:12px;padding:11px 13px;font-size:13.5px;outline:none;transition:border-color .12s, box-shadow .12s, background .12s}
.toolbar-grow input:focus,.toolbar-field input:focus,.toolbar-field select:focus,.toolbar-field textarea:focus,.form-group input:focus,.form-group select:focus,.form-group textarea:focus{border-color:rgba(163,29,30,.54);box-shadow:0 0 0 3px rgba(163,29,30,.12);background:#151b24}
.toolbar-actions{display:flex;gap:8px;justify-content:flex-end;flex-wrap:wrap}
.filter-note{font-size:12.5px;color:#9ca7ba}
.page-head{display:flex;justify-content:space-between;align-items:flex-end;gap:16px;flex-wrap:wrap;margin:0 0 18px}
.page-head-copy h2{font-size:28px;line-height:1.02;letter-spacing:-.04em;margin:0 0 8px}
.page-head-copy p{font-size:14px;color:#97a0b1;max-width:760px;margin:0}
.page-head-actions{display:flex;gap:10px;flex-wrap:wrap}
.page-shell{display:flex;flex-direction:column;gap:18px}
.section-block{display:flex;flex-direction:column;gap:14px}
.stat-card{position:relative;background:linear-gradient(180deg,#171c24 0%,#171b22 100%);border:1px solid var(--line);border-radius:20px;padding:18px 18px 16px;overflow:hidden;box-shadow:var(--shadow)}
.sc-label{font-size:12px;font-weight:700;color:#94a0b4;text-transform:uppercase;letter-spacing:.08em}
.sc-value{margin-top:6px;font-size:30px;font-weight:800;letter-spacing:-.04em;color:#f6f8fb}
.sc-icon{position:absolute;top:16px;right:16px;opacity:.16}
.sc-icon svg{width:34px;height:34px}
.sc-bar{height:4px;border-radius:999px;background:linear-gradient(90deg,#a31d1e 0%,rgba(163,29,30,.1) 100%);margin-top:14px}
.asset-summary{display:flex;flex-direction:column;gap:8px}
.asset-title-row{display:flex;align-items:flex-start;justify-content:space-between;gap:10px}
.asset-stack{display:flex;flex-direction:column;gap:3px;min-width:0;flex:1}
.asset-stack .asset-name{display:block;font-size:14px;font-weight:700;color:#f4f7fb;line-height:1.25;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.asset-stack .asset-sub{display:block;font-size:12px;color:#8690a2;margin:0;line-height:1.3}
.asset-meta,.asset-tech{display:flex;gap:8px;flex-wrap:wrap}
.asset-pill,.asset-kv{display:inline-flex;align-items:center;gap:6px;min-height:30px;padding:6px 10px;border-radius:999px;background:#1a202a;border:1px solid var(--line2);font-size:12px;color:#d7deeb;line-height:1.2}
.asset-pill svg,.asset-kv svg{width:13px;height:13px;color:#93a0b5}
.asset-kv strong{font-weight:700;color:#f2f6fb}
.act-btns{display:flex;gap:8px;align-items:center}
.form-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}
.form-group{display:flex;flex-direction:column;gap:8px}
.form-group.full{grid-column:1 / -1}
.form-group label{font-size:12px;font-weight:700;color:#a9b2c3;letter-spacing:.01em}
.form-group textarea{min-height:120px;resize:vertical}
.req{color:#f28b8b}
.form-actions{display:flex;gap:10px;justify-content:flex-end;flex-wrap:wrap;margin-top:18px}
.kpi-grid{display:grid;grid-template-columns:repeat(4,minmax(0,1fr));gap:14px}
.duo-grid{display:grid;grid-template-columns:1.35fr .95fr;gap:14px}
.half-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:14px}
.mini-bar-list{display:flex;align-items:flex-end;gap:10px;height:146px;padding-top:8px}
.mini-bar-item{flex:1;display:flex;flex-direction:column;align-items:center;gap:6px;min-width:0}
.mini-bar-col{width:100%;border-radius:8px 8px 3px 3px;background:linear-gradient(180deg,#bf2122 0%,#7c1617 100%)}
.mini-bar-label,.mini-bar-value{font-family:var(--font-mono);font-size:10px;color:#8d98aa}
.empty-state{padding:42px 22px}
.empty-state svg{width:26px;height:26px}
.pagination-wrap{display:flex;justify-content:space-between;align-items:center;gap:12px;padding:16px 18px;border-top:1px solid var(--line);flex-wrap:wrap}
.pagination{display:flex;gap:8px;flex-wrap:wrap}
.pagination a,.pagination span{display:inline-flex;align-items:center;justify-content:center;min-width:36px;height:36px;padding:0 12px;border-radius:11px;border:1px solid var(--line2);background:#1a202a;color:#d7deeb;text-decoration:none;font-size:12.5px;font-weight:700}
.pagination .active{background:linear-gradient(180deg,#b52223 0%,#9f1d1e 100%);border-color:rgba(163,29,30,.4);color:#fff}
.profile-grid{display:grid;grid-template-columns:minmax(340px,.9fr) 1.1fr;gap:16px}
@media (max-width:1100px){.toolbar-shell{grid-template-columns:1fr 1fr;}.toolbar-actions{grid-column:1 / -1;justify-content:flex-start}.kpi-grid{grid-template-columns:repeat(2,minmax(0,1fr))}.duo-grid,.half-grid,.profile-grid{grid-template-columns:1fr}}
@media (max-width:820px){.sidebar{width:min(86vw,320px)}.content-wrap{padding:18px 14px 22px}.toolbar-shell{grid-template-columns:1fr}.page-head-copy h2{font-size:24px}.kpi-grid{grid-template-columns:1fr}.form-grid{grid-template-columns:1fr}}


/* ================================
   Global framework layer / overrides
   ================================ */
:root{
  --app-sidebar-w:244px;
}
html,body{min-height:100%;}
body.theme-light{
  --bg:#f3f6fb;
  --bg2:#ffffff;
  --bg3:#f8fafc;
  --bg4:#e8edf5;
  --bg5:#d7deea;
  --line:#d9e1ee;
  --line2:#c8d2e3;
  --text:#152033;
  --muted:#4b5a73;
  --muted2:#6d7a90;
  background:linear-gradient(180deg,#eef3f9 0%,#f8fbff 100%);
  color:var(--text);
}
body.theme-light .topbar{background:rgba(255,255,255,.94)}
body.theme-light .card,
body.theme-light .metric-card,
body.theme-light .sidebar-user-card,
body.theme-light .nav-link .ni,
body.theme-light .toolbar-shell,
body.theme-light .filters,
body.theme-light .stat-card,
body.theme-light .hero-chip,
body.theme-light .topbar-title-icon,
body.theme-light .empty-state,
body.theme-light .table-wrap,
body.theme-light .panel{
  box-shadow:none;
}
.topbar-inner,
.content{max-width:none !important;width:100% !important;}
.content-wrap{justify-content:stretch !important;padding:24px 24px 30px !important;}
.content{flex:1 1 auto;}
.main{width:calc(100vw - var(--sidebar, var(--app-sidebar-w))) !important;max-width:none !important;min-width:0;}
.table-wrap,.table-responsive,.card,.toolbar-shell,.hero-grid,.dashboard-grid,.kpi-grid,.dashboard-panels,.dashboard-panels-2{max-width:100%;}
.table-wrap table, .table-responsive table{width:100%;}
/* broader desktop spacing */
@media (min-width: 1280px){
  .content-wrap{padding-left:28px !important;padding-right:28px !important;}
  .topbar-inner{padding-left:28px !important;padding-right:28px !important;}
}
/* financial/report tables: allow more width and denser columns */
.report-table table, .pivot-table table, .wide-table table{table-layout:auto !important;}
.report-table th:first-child, .report-table td:first-child,
.pivot-table th:first-child, .pivot-table td:first-child,
.wide-table th:first-child, .wide-table td:first-child{width:240px; max-width:240px;}
.report-table th, .report-table td,
.pivot-table th, .pivot-table td,
.wide-table th, .wide-table td{padding:10px 12px; font-size:13px;}
/* general table readability */
table th{font-size:11px;}
table td{font-size:13px;}
/* theme toggle button */
.theme-toggle{display:inline-flex;align-items:center;gap:8px;border:1px solid var(--line2);background:#1b2029;color:var(--muted);padding:10px 12px;border-radius:12px;font-size:12px;font-weight:700;cursor:pointer;transition:all .15s ease}
.theme-toggle:hover{background:#222938;color:var(--text);border-color:#434b5d}
.theme-toggle svg{width:15px;height:15px}
body.theme-light .theme-toggle{background:#fff;color:#334155}
/* utility wrappers */
.page-section{margin-bottom:16px;}
.page-grid-2{display:grid;grid-template-columns:1.25fr .95fr;gap:16px}
.page-grid-3{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:16px}
@media (max-width: 1100px){
  .page-grid-2,.page-grid-3{grid-template-columns:1fr;}
}



/* ===== AJUSTES VISUAIS ===== */

/* Garante que fundos decorativos fiquem atrás do conteúdo */
body {
    position: relative;
}

body::before,
body::after,
.grid-bg,
.bg-grid,
.background-grid,
.pattern-grid,
.page-grid,
.dashboard-grid-bg {
    z-index: 0 !important;
    pointer-events: none !important;
}

.main,
.main-content,
.content,
.content-wrapper,
.page-content,
.card,
.table-responsive,
.sidebar,
.topbar,
.navbar {
    position: relative;
    z-index: 2;
}

/* Menu com hover menos estourado */
.sidebar .nav-link,
.sidebar a,
.menu a,
.sidebar-menu a {
    transition: color .2s ease, background-color .2s ease, border-color .2s ease;
}

.sidebar .nav-link:hover,
.sidebar a:hover,
.menu a:hover,
.sidebar-menu a:hover {
    color: #e5e7eb !important;
    background-color: rgba(255,255,255,0.08) !important;
}

body.light .sidebar .nav-link:hover,
body.light .sidebar a:hover,
body.light .menu a:hover,
body.light .sidebar-menu a:hover {
    color: #1f2937 !important;
    background-color: rgba(0,0,0,0.06) !important;
}



/* ===== CORREÇÃO DE SIDEBAR / FULL WIDTH / GRID ===== */
html, body {
    min-height: 100%;
    overflow-x: hidden;
}

body::before,
body::after {
    z-index: 0 !important;
    pointer-events: none !important;
}

.sidebar,
.topbar,
.main,
.content-wrap,
.content {
    position: relative;
}

.sidebar {
    width: 262px !important;
    transform: none !important;
    z-index: 320 !important;
    overflow: hidden;
}

.sidebar-logo,
.sidebar-scroll,
.sidebar-footer,
.sidebar .nav-section,
.sidebar .nav-stack,
.sidebar .nav-link {
    position: relative;
    z-index: 2;
}

.sidebar-scroll {
    flex: 1 1 auto;
    overflow-y: auto;
    overflow-x: hidden;
    padding-bottom: 12px;
}

.nav-link {
    color: var(--muted) !important;
    background: transparent;
}

.nav-link:hover {
    color: #e7edf7 !important;
    background: rgba(255,255,255,.045) !important;
    border-color: rgba(255,255,255,.08) !important;
}

.nav-link.active {
    color: #fff !important;
}

.main {
    margin-left: 262px !important;
    width: calc(100% - 262px) !important;
    min-width: 0;
    z-index: 2;
}

.topbar-inner,
.content {
    max-width: none !important;
    width: 100% !important;
}

.content-wrap {
    justify-content: flex-start !important;
    padding: 22px 18px 28px !important;
}

.content {
    margin: 0 !important;
}

@media (max-width: 1100px) {
    .sidebar {
        width: 248px !important;
    }
    .main {
        margin-left: 248px !important;
        width: calc(100% - 248px) !important;
    }
    .topbar-inner,
    .content-wrap {
        padding-left: 16px !important;
        padding-right: 16px !important;
    }
}

@media (max-width: 900px) {
    .sidebar {
        transform: translateX(-100%) !important;
    }
    .sidebar.open {
        transform: translateX(0) !important;
    }
    .sidebar-overlay.open {
        display: block !important;
    }
    .main {
        margin-left: 0 !important;
        width: 100% !important;
    }
    .menu-toggle {
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
    }
}


/* ===== FINAL SIDEBAR HARD FIX ===== */
body{
  display:block !important;
  min-height:100vh !important;
  margin:0 !important;
  padding:0 !important;
  align-items:stretch !important;
  justify-content:flex-start !important;
}
.sidebar-overlay{z-index:300 !important;}
.sidebar{
  position:fixed !important;
  top:0 !important;
  left:0 !important;
  bottom:0 !important;
  width:262px !important;
  height:100vh !important;
  min-height:100vh !important;
  display:flex !important;
  flex-direction:column !important;
  justify-content:flex-start !important;
  align-items:stretch !important;
  overflow:hidden !important;
  z-index:320 !important;
  background:linear-gradient(180deg,#171b22 0%,#151922 100%) !important;
  border-right:1px solid rgba(255,255,255,.08) !important;
}
.sidebar-logo{
  flex:0 0 auto !important;
  display:flex !important;
  align-items:center !important;
  justify-content:center !important;
  min-height:74px !important;
  padding:14px 16px !important;
}
.sidebar-scroll{
  display:block !important;
  flex:1 1 auto !important;
  min-height:0 !important;
  overflow-y:auto !important;
  overflow-x:hidden !important;
  padding:10px 12px !important;
}
.sidebar-footer{
  flex:0 0 auto !important;
  display:flex !important;
  flex-direction:column !important;
  gap:10px !important;
  padding:12px !important;
  margin-top:auto !important;
}
.sidebar .nav-section,
.sidebar .nav-stack,
.sidebar .nav-link,
.sidebar .nav-meta,
.sidebar .nav-label{
  position:relative !important;
  z-index:2 !important;
  opacity:1 !important;
  visibility:visible !important;
}
.main{
  display:block !important;
  position:relative !important;
  margin-left:262px !important;
  width:calc(100% - 262px) !important;
  min-height:100vh !important;
}
.topbar,
.content-wrap,
.content{
  width:100% !important;
  max-width:none !important;
}
.content-wrap{
  display:block !important;
  padding:22px 18px 28px !important;
}
@media (max-width: 900px){
  .sidebar{transform:translateX(-100%) !important;}
  .sidebar.open{transform:translateX(0) !important;}
  .main{margin-left:0 !important; width:100% !important;}
}


/* ===== CORREÇÃO FINAL TABELAS DO FINANCEIRO ===== */
.fin-table{
    table-layout:fixed !important;
    width:100% !important;
}
.fin-table th,
.fin-table td{
    overflow:hidden;
}
.fin-table th:first-child,
.fin-table td:first-child{
    width:320px !important;
    max-width:320px !important;
    min-width:320px !important;
    white-space:nowrap !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
}
.fin-table th:not(:first-child),
.fin-table td:not(:first-child){
    width:140px !important;
    min-width:140px !important;
    text-align:right !important;
}
.fin-table.table-pivot th:nth-child(2),
.fin-table.table-pivot td:nth-child(2){
    width:160px !important;
    min-width:160px !important;
}
@media (max-width: 1280px){
    .fin-table th:first-child,
    .fin-table td:first-child{
        width:280px !important;
        min-width:280px !important;
        max-width:280px !important;
    }
    .fin-table th:not(:first-child),
    .fin-table td:not(:first-child){
        width:120px !important;
        min-width:120px !important;
    }
}




/* ===== RANKING RESUMO POR EMPRESA ===== */
.fin-ranking-list{display:flex;flex-direction:column;gap:10px}
.fin-ranking-row{
    display:grid;
    grid-template-columns:minmax(0,1.3fr) minmax(320px,.9fr);
    gap:16px;
    align-items:center;
    padding:14px 16px;
    border:1px solid rgba(255,255,255,.05);
    border-radius:16px;
    background:linear-gradient(180deg,rgba(255,255,255,.02),rgba(255,255,255,.015));
    transition:transform .18s ease, background-color .18s ease, border-color .18s ease;
}
.fin-ranking-row:hover{
    transform:translateY(-1px);
    border-color:rgba(255,255,255,.09);
    background:linear-gradient(180deg,rgba(255,255,255,.035),rgba(255,255,255,.02));
}
.fin-ranking-main{min-width:0}
.fin-ranking-topline{display:flex;align-items:center;gap:10px;margin-bottom:8px}
.fin-ranking-pos{
    display:inline-flex;align-items:center;justify-content:center;
    width:28px;height:28px;border-radius:999px;
    background:linear-gradient(180deg,rgba(163,29,30,.34),rgba(163,29,30,.14));
    border:1px solid rgba(163,29,30,.35);
    color:#fff;font-size:11px;font-weight:800;flex:0 0 28px;
}
.fin-ranking-name{
    min-width:0;
    overflow:hidden;text-overflow:ellipsis;white-space:nowrap;
    color:#f2f6fb;font-size:14px;font-weight:700;letter-spacing:-.01em;
}
.fin-ranking-bar{
    height:10px;border-radius:999px;overflow:hidden;
    background:rgba(255,255,255,.05);
}
.fin-ranking-fill{
    height:100%;border-radius:999px;
    background:linear-gradient(90deg,rgba(163,29,30,.8),rgba(255,124,124,.96));
    box-shadow:0 0 18px rgba(163,29,30,.18);
}
.fin-ranking-metrics{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:10px;
}
.fin-ranking-metric{
    padding:10px 12px;border-radius:12px;
    border:1px solid rgba(255,255,255,.05);
    background:rgba(255,255,255,.025);
}
.fin-ranking-metric .m-label{
    display:block;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#8fa1bf;
    font-weight:800;
    margin-bottom:5px;
}
.fin-ranking-metric .m-value{
    display:block;
    color:#fff;
    font-size:13px;
    line-height:1.2;
    font-weight:800;
}
.fin-ranking-metric.total{
    background:linear-gradient(180deg,rgba(163,29,30,.14),rgba(163,29,30,.05));
    border-color:rgba(163,29,30,.24);
}
.fin-ranking-metric.total .m-value{font-size:14px}
@media (max-width: 1180px){
    .fin-ranking-row{grid-template-columns:1fr}
    .fin-ranking-metrics{grid-template-columns:repeat(3,minmax(0,1fr))}
}
@media (max-width: 720px){
    .fin-ranking-metrics{grid-template-columns:1fr}
}



/* ===== FINANCEIRO | ABAS EM FORMATO EXECUTIVO ===== */
.fin-empty{
    padding:28px 18px;
    border:1px dashed rgba(255,255,255,.12);
    border-radius:18px;
    color:#9fafc4;
    text-align:center;
    background:rgba(255,255,255,.02);
}
.fin-matrix-list,
.fin-series-list{
    display:flex;
    flex-direction:column;
    gap:12px;
}
.fin-matrix-row,
.fin-series-row{
    border:1px solid rgba(255,255,255,.06);
    border-radius:18px;
    background:linear-gradient(180deg,rgba(255,255,255,.022),rgba(255,255,255,.015));
    padding:16px;
    box-shadow:0 10px 26px rgba(0,0,0,.14);
}
.fin-series-row.compact{
    padding-top:14px;
    padding-bottom:14px;
}
.fin-matrix-main{
    margin-bottom:14px;
}
.fin-matrix-topline,
.fin-series-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:12px;
    margin-bottom:10px;
}
.fin-matrix-name,
.series-name{
    display:block;
    font-size:15px;
    font-weight:800;
    color:#f3f7fc;
    line-height:1.35;
}
.fin-matrix-total,
.series-total{
    flex:0 0 auto;
    padding:8px 12px;
    border-radius:999px;
    background:rgba(163,29,30,.12);
    border:1px solid rgba(163,29,30,.22);
    color:#ffe2e2;
    font-size:12px;
    font-weight:800;
    white-space:nowrap;
}
.fin-matrix-bar{
    height:10px;
    border-radius:999px;
    background:rgba(255,255,255,.05);
    overflow:hidden;
}
.fin-matrix-fill{
    height:100%;
    border-radius:999px;
    background:linear-gradient(90deg,rgba(163,29,30,.86),rgba(255,132,132,.96));
}
.fin-matrix-chips,
.fin-chip-grid{
    display:grid;
    grid-template-columns:repeat(auto-fit,minmax(130px,1fr));
    gap:10px;
}
.fin-chip-card{
    padding:11px 12px;
    border-radius:14px;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.05);
    min-width:0;
}
.fin-chip-card .c-label{
    display:block;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#90a0b8;
    font-weight:700;
    margin-bottom:6px;
}
.fin-chip-card .c-value{
    display:block;
    font-size:13px;
    line-height:1.3;
    color:#f4f8fd;
    font-weight:800;
    overflow:hidden;
    text-overflow:ellipsis;
    white-space:nowrap;
}
.fin-series-title{
    min-width:0;
}
.series-meta{
    display:block;
    margin-top:4px;
    font-size:12px;
    color:#93a1b7;
    line-height:1.45;
}
.fin-matrix-list.money .fin-matrix-total,
.fin-series-list.counter .series-total{
    background:rgba(20,97,255,.09);
    border-color:rgba(91,142,255,.18);
    color:#dfe9ff;
}
.fin-detail-table{
    min-width:3200px;
}
.fin-detail-table th,
.fin-detail-table td{
    white-space:nowrap;
}
.fin-detail-table tbody tr:nth-child(even) td{
    background:rgba(255,255,255,.01);
}
.fin-detail-table tbody tr:hover td{
    background:rgba(255,255,255,.028);
}
@media (max-width: 900px){
    .fin-matrix-topline,
    .fin-series-head{
        flex-direction:column;
        align-items:flex-start;
    }
    .fin-matrix-chips,
    .fin-chip-grid{
        grid-template-columns:repeat(2,minmax(0,1fr));
    }
}
@media (max-width: 640px){
    .fin-matrix-chips,
    .fin-chip-grid{
        grid-template-columns:1fr;
    }
}



/* ===== CORREÇÃO FORTE DAS ABAS EM CARDS ===== */
.fin-series-list,
.fin-matrix-list{
    display:flex !important;
    flex-direction:column !important;
    gap:14px !important;
}

.fin-series-row,
.fin-matrix-row{
    display:block !important;
    padding:18px !important;
    border:1px solid rgba(255,255,255,.06) !important;
    border-radius:18px !important;
    background:linear-gradient(180deg,rgba(255,255,255,.022),rgba(255,255,255,.015)) !important;
    box-shadow:0 10px 26px rgba(0,0,0,.14) !important;
}

.fin-series-head,
.fin-matrix-topline{
    display:flex !important;
    align-items:flex-start !important;
    justify-content:space-between !important;
    gap:12px !important;
    margin-bottom:10px !important;
}

.fin-series-title{
    display:block !important;
    min-width:0 !important;
    flex:1 1 auto !important;
}

.series-name,
.fin-matrix-name{
    display:block !important;
    font-size:15px !important;
    font-weight:800 !important;
    color:#f3f7fc !important;
    line-height:1.35 !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    white-space:nowrap !important;
}

.series-meta{
    display:block !important;
    margin-top:4px !important;
    font-size:12px !important;
    color:#93a1b7 !important;
    line-height:1.45 !important;
}

.series-total,
.fin-matrix-total{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    flex:0 0 auto !important;
    padding:8px 12px !important;
    border-radius:999px !important;
    background:rgba(163,29,30,.12) !important;
    border:1px solid rgba(163,29,30,.22) !important;
    color:#ffe2e2 !important;
    font-size:12px !important;
    font-weight:800 !important;
    white-space:nowrap !important;
}

.fin-matrix-main{
    margin-bottom:14px !important;
}

.fin-matrix-bar{
    display:block !important;
    height:10px !important;
    border-radius:999px !important;
    background:rgba(255,255,255,.05) !important;
    overflow:hidden !important;
    margin:10px 0 14px !important;
}

.fin-matrix-fill{
    display:block !important;
    height:100% !important;
    border-radius:999px !important;
    background:linear-gradient(90deg,rgba(163,29,30,.86),rgba(255,132,132,.96)) !important;
}

.fin-chip-grid,
.fin-matrix-chips{
    display:grid !important;
    grid-template-columns:repeat(auto-fit,minmax(130px,1fr)) !important;
    gap:10px !important;
    margin-top:14px !important;
}

.fin-chip-card{
    display:block !important;
    padding:11px 12px !important;
    border-radius:14px !important;
    background:rgba(255,255,255,.03) !important;
    border:1px solid rgba(255,255,255,.05) !important;
    min-width:0 !important;
}

.fin-chip-card .c-label{
    display:block !important;
    font-size:10px !important;
    text-transform:uppercase !important;
    letter-spacing:.08em !important;
    color:#90a0b8 !important;
    font-weight:700 !important;
    margin-bottom:6px !important;
}

.fin-chip-card .c-value{
    display:block !important;
    font-size:13px !important;
    line-height:1.3 !important;
    color:#f4f8fd !important;
    font-weight:800 !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    white-space:nowrap !important;
}

.fin-series-list.counter .series-total{
    background:rgba(20,97,255,.09) !important;
    border-color:rgba(91,142,255,.18) !important;
    color:#dfe9ff !important;
}

.fin-matrix-list.money .fin-matrix-total{
    background:rgba(20,97,255,.09) !important;
    border-color:rgba(91,142,255,.18) !important;
    color:#dfe9ff !important;
}

@media (max-width: 900px){
    .fin-series-head,
    .fin-matrix-topline{
        flex-direction:column !important;
        align-items:flex-start !important;
    }
    .fin-chip-grid,
    .fin-matrix-chips{
        grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    }
}

@media (max-width: 640px){
    .fin-chip-grid,
    .fin-matrix-chips{
        grid-template-columns:1fr !important;
    }
}



/* ===== FALLBACK FINAL | FINANCEIRO EM CARDS ===== */
body .fin-page .fin-series-list,
body .fin-page .fin-matrix-list{
    display:flex !important;
    flex-direction:column !important;
    gap:14px !important;
}
body .fin-page .fin-series-row,
body .fin-page .fin-matrix-row{
    display:block !important;
    padding:18px !important;
    margin:0 !important;
    border:1px solid rgba(255,255,255,.06) !important;
    border-radius:18px !important;
    background:linear-gradient(180deg,rgba(255,255,255,.022),rgba(255,255,255,.015)) !important;
    box-shadow:0 10px 26px rgba(0,0,0,.14) !important;
}
body .fin-page .fin-series-head,
body .fin-page .fin-matrix-topline{
    display:flex !important;
    align-items:flex-start !important;
    justify-content:space-between !important;
    gap:12px !important;
    margin-bottom:10px !important;
}
body .fin-page .fin-series-title{
    display:block !important;
    min-width:0 !important;
    flex:1 1 auto !important;
}
body .fin-page .series-name,
body .fin-page .fin-matrix-name{
    display:block !important;
    font-size:15px !important;
    font-weight:800 !important;
    color:#f3f7fc !important;
    line-height:1.35 !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    white-space:nowrap !important;
}
body .fin-page .series-meta{
    display:block !important;
    margin-top:4px !important;
    font-size:12px !important;
    color:#93a1b7 !important;
    line-height:1.45 !important;
}
body .fin-page .series-total,
body .fin-page .fin-matrix-total{
    display:inline-flex !important;
    align-items:center !important;
    justify-content:center !important;
    flex:0 0 auto !important;
    padding:8px 12px !important;
    border-radius:999px !important;
    background:rgba(163,29,30,.12) !important;
    border:1px solid rgba(163,29,30,.22) !important;
    color:#ffe2e2 !important;
    font-size:12px !important;
    font-weight:800 !important;
    white-space:nowrap !important;
}
body .fin-page .fin-matrix-main{
    margin-bottom:14px !important;
}
body .fin-page .fin-matrix-bar{
    display:block !important;
    height:10px !important;
    border-radius:999px !important;
    background:rgba(255,255,255,.05) !important;
    overflow:hidden !important;
    margin:10px 0 14px !important;
}
body .fin-page .fin-matrix-fill{
    display:block !important;
    height:100% !important;
    border-radius:999px !important;
    background:linear-gradient(90deg,rgba(163,29,30,.86),rgba(255,132,132,.96)) !important;
}
body .fin-page .fin-chip-grid,
body .fin-page .fin-matrix-chips{
    display:grid !important;
    grid-template-columns:repeat(auto-fit,minmax(130px,1fr)) !important;
    gap:10px !important;
    margin-top:14px !important;
}
body .fin-page .fin-chip-card{
    display:block !important;
    padding:11px 12px !important;
    border-radius:14px !important;
    background:rgba(255,255,255,.03) !important;
    border:1px solid rgba(255,255,255,.05) !important;
    min-width:0 !important;
}
body .fin-page .fin-chip-card .c-label{
    display:block !important;
    font-size:10px !important;
    text-transform:uppercase !important;
    letter-spacing:.08em !important;
    color:#90a0b8 !important;
    font-weight:700 !important;
    margin-bottom:6px !important;
}
body .fin-page .fin-chip-card .c-value{
    display:block !important;
    font-size:13px !important;
    line-height:1.3 !important;
    color:#f4f8fd !important;
    font-weight:800 !important;
    overflow:hidden !important;
    text-overflow:ellipsis !important;
    white-space:nowrap !important;
}
@media (max-width:900px){
    body .fin-page .fin-series-head,
    body .fin-page .fin-matrix-topline{
        flex-direction:column !important;
        align-items:flex-start !important;
    }
    body .fin-page .fin-chip-grid,
    body .fin-page .fin-matrix-chips{
        grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    }
}
@media (max-width:640px){
    body .fin-page .fin-chip-grid,
    body .fin-page .fin-matrix-chips{
        grid-template-columns:1fr !important;
    }
}


/* ===== FINANCEIRO | COMPARAÇÃO LIMPA ENTRE MESES ===== */
.fin-compare-table{width:100%;min-width:100%;}
.fin-compare-table th:first-child,.fin-compare-table td:first-child{min-width:320px;width:320px;}
.fin-compare-table th:not(:first-child),.fin-compare-table td:not(:first-child){min-width:150px;width:150px;}
.fin-row-sub{display:block;margin-top:4px;font-size:12px;color:#93a1b7;line-height:1.4;}
.fin-compare-table td.delta{font-weight:800;}
.fin-compare-table td.delta.up{color:#8ef0c7;}
.fin-compare-table td.delta.down{color:#ffb1b1;}
.fin-compare-table td.delta.neutral{color:#dce5f4;}
.fin-compare-table.money td,.fin-compare-table.counter td{font-variant-numeric:tabular-nums;}



/* ===== FINANCEIRO | GERAL COM LEITURA MAIS CLARA ===== */
.fin-inline-stats{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:12px;
    margin:0 0 16px;
}
.fin-inline-stat{
    padding:12px 14px;
    border-radius:16px;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.05);
}
.fin-inline-stat .s-label{
    display:block;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#90a0b8;
    font-weight:700;
    margin-bottom:6px;
}
.fin-inline-stat .s-value{
    display:block;
    font-size:16px;
    line-height:1.25;
    color:#f4f8fd;
    font-weight:800;
}
.fin-inline-stat .s-sub{
    display:block;
    margin-top:4px;
    font-size:12px;
    color:#93a1b7;
}
.fin-chart-caption{
    margin-top:10px;
    font-size:12px;
    color:#93a1b7;
    line-height:1.45;
}
@media (max-width:900px){
    .fin-inline-stats{grid-template-columns:1fr;}
}



.fin-total-row td{
    background:rgba(255,255,255,.045) !important;
    border-top:1px solid rgba(255,255,255,.08);
    font-weight:800;
}
.fin-total-row td strong{
    color:#ffffff !important;
}



/* ===== FINANCEIRO | GERAL REDESENHADA ===== */
.fin-geral-shell{
    display:grid;
    grid-template-columns:1.35fr .95fr;
    gap:18px;
    align-items:start;
}
.fin-geral-charts-grid{
    display:grid;
    grid-template-columns:1fr;
    gap:18px;
}
.fin-geral-wide-chart .fin-chart-box{
    height:340px;
}
.fin-chart-box.short{
    height:300px;
}
.fin-geral-total-strip{
    display:grid;
    grid-template-columns:repeat(3,minmax(0,1fr));
    gap:12px;
    margin-top:16px;
    padding-top:16px;
    border-top:1px solid rgba(255,255,255,.06);
}
.fin-geral-total-strip .t-item{
    padding:12px 14px;
    border-radius:16px;
    background:rgba(255,255,255,.03);
    border:1px solid rgba(255,255,255,.05);
}
.fin-geral-total-strip .t-item.total{
    background:rgba(163,29,30,.08);
    border-color:rgba(163,29,30,.18);
}
.fin-geral-total-strip .t-label{
    display:block;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#90a0b8;
    font-weight:700;
    margin-bottom:6px;
}
.fin-geral-total-strip .t-value{
    display:block;
    font-size:16px;
    line-height:1.25;
    color:#f4f8fd;
    font-weight:800;
}
@media (max-width: 1300px){
    .fin-geral-shell{
        grid-template-columns:1fr;
    }
}
@media (max-width: 760px){
    .fin-geral-total-strip{
        grid-template-columns:1fr;
    }
}



.fin-geral-shell.simple{
    grid-template-columns:1fr;
}
.fin-geral-charts-grid.one-col{
    grid-template-columns:1fr;
}
.fin-chart-box.tall{
    height:360px;
}



.fin-geral-total-strip.top{
    margin-bottom:18px;
    margin-top:0;
    padding-top:0;
    border-top:0;
}



.fin-inline-stats.per-tab{
    margin:0 0 16px;
}
.fin-inline-stat.status-up{
    background:rgba(20,160,110,.08);
    border-color:rgba(20,160,110,.18);
}
.fin-inline-stat.status-down{
    background:rgba(190,44,44,.10);
    border-color:rgba(190,44,44,.18);
}
.fin-inline-stat.status-neutral{
    background:rgba(88,110,145,.09);
    border-color:rgba(88,110,145,.18);
}



/* ===== AJUSTES FINAIS | LOGIN / GRADE / LAYOUT ===== */
body.login-page{
    min-height:100vh;
    display:flex;
    align-items:center;
    justify-content:center;
    padding:24px;
    background:
        radial-gradient(circle at top, rgba(163,29,30,.18), transparent 32%),
        linear-gradient(180deg,#0b0f15 0%,#101722 100%);
    position:relative;
    isolation:isolate;
}
body.login-page::before{
    z-index:0 !important;
    opacity:.24;
}
body.login-page .box{
    z-index:2;
}
body.login-page .ft,
body.login-page label,
body.login-page input,
body.login-page .logo-area,
body.login-page .btn-in,
body.login-page .err,
body.login-page .info{
    position:relative;
    z-index:2;
}

/* grade sempre atrás do conteúdo */
body{
    isolation:isolate;
}
body::before,
body::after{
    z-index:0 !important;
}
.content-wrap,
.page-shell,
.page-head,
.card,
.table-wrap,
.fin-table-wrap,
.table-responsive,
.overview-card,
.analytics-card{
    position:relative;
    z-index:1;
}
.table-wrap,
.fin-table-wrap{
    background:linear-gradient(180deg,rgba(16,22,33,.98),rgba(13,19,29,.98));
}
table,
.dtbl,
.fin-table{
    position:relative;
    z-index:1;
}
thead th,
.dtbl thead th,
.fin-table thead th{
    position:relative;
    z-index:2;
}
tbody td,
.dtbl tbody td,
.fin-table tbody td{
    background:rgba(14,20,30,.96);
    position:relative;
    z-index:1;
}
tbody tr:nth-child(even) td,
.dtbl tbody tr:nth-child(even) td,
.fin-table tbody tr:nth-child(even) td{
    background:rgba(17,24,35,.98);
}

/* computadores e celulares: usar melhor largura */
.toolbar-shell{
    grid-template-columns:minmax(340px,2fr) minmax(240px,1fr) minmax(240px,1fr) auto !important;
    gap:12px !important;
}
.card .table-wrap{
    width:100%;
}
table{
    min-width:100%;
}
@media (max-width: 1180px){
    .toolbar-shell{
        grid-template-columns:1fr 1fr !important;
    }
    .toolbar-actions{
        justify-content:flex-start !important;
    }
}
@media (max-width: 760px){
    .toolbar-shell{
        grid-template-columns:1fr !important;
    }
}

/* tabelas de computadores/celulares mais respiráveis */
.table-wrap table thead th{
    font-size:11px;
}
.table-wrap table td{
    font-size:13px;
}
.table-wrap table td,
.table-wrap table th{
    padding:12px 14px;
}

/* distribuição: filtros e tabela mais bem distribuídos */
.dist-filters{
    grid-template-columns:minmax(260px,2fr) repeat(7,minmax(150px,1fr)) auto !important;
    gap:12px !important;
}
.table-wrap.wide-table{
    overflow:auto;
}
.table-wrap.wide-table .dtbl{
    min-width:1800px;
}
.table-wrap.wide-table .dtbl th:nth-child(1),
.table-wrap.wide-table .dtbl td:nth-child(1){
    min-width:240px;
}
.table-wrap.wide-table .dtbl th:nth-child(2),
.table-wrap.wide-table .dtbl td:nth-child(2){
    min-width:130px;
}
.table-wrap.wide-table .dtbl th:nth-child(3),
.table-wrap.wide-table .dtbl td:nth-child(3){
    min-width:190px;
}
.table-wrap.wide-table .dtbl th:nth-child(4),
.table-wrap.wide-table .dtbl td:nth-child(4){
    min-width:230px;
}
.table-wrap.wide-table .dtbl th:nth-child(5),
.table-wrap.wide-table .dtbl td:nth-child(5){
    min-width:150px;
}
.table-wrap.wide-table .dtbl th:nth-child(6),
.table-wrap.wide-table .dtbl td:nth-child(6),
.table-wrap.wide-table .dtbl th:nth-child(7),
.table-wrap.wide-table .dtbl td:nth-child(7){
    min-width:140px;
}
.table-wrap.wide-table .dtbl th:nth-child(8),
.table-wrap.wide-table .dtbl td:nth-child(8),
.table-wrap.wide-table .dtbl th:nth-child(9),
.table-wrap.wide-table .dtbl td:nth-child(9),
.table-wrap.wide-table .dtbl th:nth-child(10),
.table-wrap.wide-table .dtbl td:nth-child(10),
.table-wrap.wide-table .dtbl th:nth-child(11),
.table-wrap.wide-table .dtbl td:nth-child(11){
    min-width:120px;
}
.table-wrap.wide-table .dtbl th:last-child,
.table-wrap.wide-table .dtbl td:last-child{
    min-width:170px;
}
.dtbl .dp{
    font-size:13px;
}
.dtbl .ds{
    font-size:11.5px;
}
.dtbl .dm{
    font-size:11px;
}
@media (max-width: 1280px){
    .dist-filters{
        grid-template-columns:repeat(4,minmax(160px,1fr)) !important;
    }
}
@media (max-width: 860px){
    .dist-filters{
        grid-template-columns:1fr 1fr !important;
    }
}
@media (max-width: 640px){
    .dist-filters{
        grid-template-columns:1fr !important;
    }
}


body.login-page{
    min-height:100vh;
}
body.login-page .login-shell{
    width:100%;
    min-height:calc(100vh - 48px);
    display:flex;
    align-items:center;
    justify-content:center;
}
body.login-page .box{
    margin:0 auto;
}



.fin-inline-stats-4{
    grid-template-columns:repeat(4,minmax(220px,1fr));
}
.fin-section-title{
    margin:18px 0 10px;
    font-size:13px;
    font-weight:800;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#9db4ff;
}
@media (max-width: 1200px){
    .fin-inline-stats-4{grid-template-columns:repeat(2,minmax(220px,1fr));}
}
@media (max-width: 680px){
    .fin-inline-stats-4{grid-template-columns:1fr;}
}


/* Ajustes v6 - financeiro impressão */
.page-shell.fin-page{
    padding: 12px 12px 18px 12px !important;
}
.fin-table-wrap{
    overflow-x:auto;
    overflow-y:hidden;
    width:100%;
    max-width:100%;
}
.fin-table{
    width:max-content;
    min-width:100%;
    table-layout:auto;
}
.fin-table.table-pivot,
.fin-table.table-wide{
    width:max-content;
    min-width:100%;
}
.fin-table th,
.fin-table td{
    padding:10px 12px;
    font-size:12.5px;
}
.fin-table.table-pivot th,
.fin-table.table-pivot td,
.fin-table.table-wide th,
.fin-table.table-wide td{
    white-space:nowrap;
}
.fin-table.table-pivot th:first-child,
.fin-table.table-pivot td:first-child,
.fin-table.table-wide th:first-child,
.fin-table.table-wide td:first-child{
    min-width:220px;
    max-width:220px;
}
.fin-table .fin-row-sub{
    display:block;
    color:#8d99ae;
    font-size:11px;
    line-height:1.35;
    margin-top:3px;
}
.fin-compare-table td.delta{
    font-weight:800;
}
.fin-compare-table td.delta.up{
    color:#22c55e;
}
.fin-compare-table td.delta.down{
    color:#ef4444;
}
.fin-compare-table td.delta.neutral{
    color:#cbd5e1;
}
.fin-inline-stat.status-up{
    border-color:rgba(34,197,94,.30);
    background:rgba(34,197,94,.08);
}
.fin-inline-stat.status-up .s-value,
.fin-inline-stat.status-up .s-sub{
    color:#86efac;
}
.fin-inline-stat.status-down{
    border-color:rgba(239,68,68,.30);
    background:rgba(239,68,68,.08);
}
.fin-inline-stat.status-down .s-value,
.fin-inline-stat.status-down .s-sub{
    color:#fca5a5;
}
.fin-inline-stat.status-neutral{
    border-color:rgba(148,163,184,.26);
    background:rgba(148,163,184,.07);
}
.fin-inline-stat.status-neutral .s-value,
.fin-inline-stat.status-neutral .s-sub{
    color:#e2e8f0;
}


/* Melhor visualização da aba de modelos */
.fin-model-grid{display:grid;grid-template-columns:1fr;gap:18px}
.fin-model-card{border:1px solid var(--line);border-radius:18px;background:linear-gradient(180deg,#151b24 0%,#121822 100%);padding:18px;box-shadow:0 12px 28px rgba(0,0,0,.16)}
.fin-model-head{display:flex;justify-content:space-between;align-items:flex-start;gap:16px;margin-bottom:12px}
.fin-model-head h4{margin:0;font-size:18px;line-height:1.2}
.fin-model-head p{margin:4px 0 0;color:#91a0b5;font-size:12px}
.fin-model-totals{display:flex;gap:10px;flex-wrap:wrap;justify-content:flex-end}
.fin-mini-kpi{min-width:140px;padding:10px 12px;border:1px solid rgba(255,255,255,.07);border-radius:14px;background:#111720}
.fin-mini-kpi span{display:block;font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#95a1b6}
.fin-mini-kpi strong{display:block;margin-top:4px;font-size:15px;color:#f5f8fb}
.fin-model-stats{display:flex;gap:10px;flex-wrap:wrap;margin-bottom:14px}
.fin-model-stat{display:flex;flex-direction:column;gap:2px;min-width:160px;padding:10px 12px;border-radius:14px;border:1px solid rgba(255,255,255,.08);background:#121a24}
.fin-model-stat .label{font-size:11px;text-transform:uppercase;letter-spacing:.06em;color:#95a1b6}
.fin-model-stat strong{font-size:15px}
.fin-model-stat small{font-size:12px;color:#aab6c8}
.fin-model-stat.status-up{border-color:rgba(34,197,94,.28);background:rgba(34,197,94,.08)}
.fin-model-stat.status-up strong,.fin-model-stat.status-up small{color:#86efac}
.fin-model-stat.status-down{border-color:rgba(239,68,68,.28);background:rgba(239,68,68,.08)}
.fin-model-stat.status-down strong,.fin-model-stat.status-down small{color:#fca5a5}
.fin-model-stat.status-neutral{border-color:rgba(148,163,184,.18);background:rgba(148,163,184,.06)}
.fin-model-table-wrap{border-radius:14px}
.fin-model-table{width:max-content;min-width:100%}
.fin-model-table th,.fin-model-table td{white-space:nowrap;text-align:right}
.fin-model-table th:first-child,.fin-model-table td:first-child{text-align:left;position:sticky;left:0;z-index:2;min-width:180px;background:#121822}
.fin-model-table thead th:first-child{background:#151a21;z-index:3}
@media (max-width:900px){.fin-model-head{flex-direction:column}.fin-model-totals{justify-content:flex-start}}


/* ===== FINANCEIRO | ABA GERAL MAIS EXPLICATIVA ===== */
.fin-geral-dashboard{
    display:grid;
    grid-template-columns:1fr;
    gap:18px;
}
.fin-geral-top-grid{
    display:grid;
    grid-template-columns:minmax(0,1.45fr) minmax(320px,.75fr);
    gap:18px;
    align-items:stretch;
}
.fin-geral-summary-grid{
    margin-top:4px;
}
.fin-geral-chart-card .fin-chart-box{
    height:220px;
}
.fin-geral-chart-grid{
    display:grid;
    grid-template-columns:minmax(0,1fr) minmax(280px,.42fr);
    gap:16px;
    align-items:start;
}
.fin-geral-chart-grid .fin-chart-box{
    margin:0;
    height:220px;
}
.fin-geral-chart-grid .fin-chart-box.side{
    height:220px;
    padding:14px 14px 10px;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.015));
}
.fin-geral-chart-side-stack{
    display:block;
}
.fin-chart-box-main-compact{
    min-height:220px;
    max-height:220px;
}
.fin-chart-box-side-compact{
    min-height:220px;
    max-height:220px;
}
.fin-chart-box-side-tall{
    height:220px;
    max-height:220px;
}
.fin-side-panel{
    padding:14px;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.015));
}
.fin-side-panel .fin-chart-mini-head.compact{
    margin-bottom:12px;
}
.fin-side-stat-grid{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:10px;
}
.fin-side-stat{
    padding:12px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.06);
    background:rgba(255,255,255,.025);
    min-height:88px;
}
.fin-side-stat .label{
    display:block;
    color:#8fa1bf;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.08em;
    font-weight:800;
    margin-bottom:8px;
}
.fin-side-stat strong{
    display:block;
    color:#edf3fa;
    font-size:15px;
    line-height:1.25;
}
.fin-side-stat small{
    display:block;
    margin-top:6px;
    color:#93a4ba;
    font-size:12px;
}
.fin-side-stat.status-up{
    border-color:rgba(34,197,94,.26);
    background:rgba(34,197,94,.08);
}
.fin-side-stat.status-up strong,
.fin-side-stat.status-up small{color:#86efac;}
.fin-side-stat.status-down{
    border-color:rgba(239,68,68,.26);
    background:rgba(239,68,68,.08);
}
.fin-side-stat.status-down strong,
.fin-side-stat.status-down small{color:#fca5a5;}
.fin-side-stat.status-neutral{
    border-color:rgba(148,163,184,.2);
    background:rgba(148,163,184,.06);
}
.fin-chart-mini-head{
    display:flex;
    align-items:flex-start;
    justify-content:space-between;
    gap:12px;
    margin-bottom:10px;
}
.fin-chart-mini-head span{
    color:#8fa1bf;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.08em;
    font-weight:800;
}
.fin-chart-mini-head strong{
    color:#e7fff4;
    font-size:15px;
    line-height:1.2;
}
.fin-geral-insights{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:12px;
    margin-top:16px;
}
.fin-geral-insights-3{
    grid-template-columns:1.2fr repeat(2,minmax(0,1fr));
}
.insight-item{
    padding:14px 16px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.06);
    background:rgba(255,255,255,.025);
}
.insight-item.emphasis{
    background:linear-gradient(135deg,rgba(52,211,153,.10),rgba(125,211,252,.06));
    border-color:rgba(52,211,153,.18);
}
.insight-foot{
    display:block;
    margin-top:8px;
    font-size:12px;
    color:#8fa1bf;
    line-height:1.45;
}
.insight-label{
    display:block;
    color:#8fa1bf;
    font-size:10px;
    text-transform:uppercase;
    letter-spacing:.08em;
    font-weight:800;
    margin-bottom:6px;
}
.insight-value{
    display:block;
    color:#edf3fa;
    font-size:13px;
    line-height:1.5;
}
.fin-mini-history{
    display:grid;
    gap:10px;
}
.mini-history-item{
    padding:12px 14px;
    border-radius:14px;
    border:1px solid rgba(255,255,255,.06);
    background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.015));
}
.mh-top,
.mh-sub{
    display:flex;
    align-items:center;
    justify-content:space-between;
    gap:12px;
}
.mh-top strong{
    color:#f4f8fd;
    font-size:13px;
}
.mh-top span{
    color:#ffd9d9;
    font-size:12px;
    font-weight:700;
}
.mh-sub{
    margin-top:6px;
}
.mh-sub span{
    color:#93a4ba;
    font-size:12px;
}
.fin-ranking-list.compact{
    gap:12px;
}
.fin-ranking-row.compact{
    grid-template-columns:minmax(0,1fr);
    gap:12px;
}
.fin-ranking-metrics.compact{
    grid-template-columns:repeat(2,minmax(0,1fr));
}
.fin-ranking-metric .m-value{
    color:#f4f8fd;
    font-size:14px;
    line-height:1.35;
}
@media (max-width: 1280px){
    .fin-geral-top-grid{
        grid-template-columns:1fr;
    }
}
@media (max-width: 1100px){
    .fin-geral-chart-grid,
    .fin-geral-insights-3{
        grid-template-columns:1fr;
    }
    .fin-geral-chart-side-stack{
        grid-template-rows:auto auto;
    }
    .fin-geral-chart-card .fin-chart-box,
    .fin-geral-chart-grid .fin-chart-box.side,
    .fin-chart-box-main-compact{
        height:240px;
        min-height:240px;
    }
    .fin-chart-box-side-compact{
        height:220px;
        min-height:220px;
    }
}
@media (max-width: 760px){
    .fin-geral-insights,
    .fin-ranking-metrics.compact,
    .fin-side-stat-grid{
        grid-template-columns:1fr;
    }
    .mh-top,
    .mh-sub,
    .fin-chart-mini-head{
        flex-direction:column;
        align-items:flex-start;
    }
    .fin-geral-chart-card .fin-chart-box,
    .fin-chart-box-main-compact{
        height:220px;
        min-height:220px;
    }
    .fin-geral-chart-grid .fin-chart-box.side,
    .fin-chart-box-side-compact{
        height:200px;
        min-height:200px;
    }
}

.fin-geral-chart-grid canvas,
.fin-geral-chart-card canvas{
    width:100% !important;
    height:100% !important;
    max-height:220px;
}
@media (max-width: 1100px){
    .fin-geral-chart-grid canvas,
    .fin-geral-chart-card canvas{
        max-height:240px;
    }
}
@media (max-width: 760px){
    .fin-geral-chart-grid canvas,
    .fin-geral-chart-card canvas{
        max-height:220px;
    }
}


/* v14 - ajuste da aba geral: gráfico maior e menos espaço vazio */
.fin-geral-chart-card{
    display:flex;
    flex-direction:column;
}
.fin-geral-chart-grid{
    grid-template-columns:minmax(0,1.18fr) minmax(300px,.42fr);
    gap:14px;
    align-items:start;
    margin-bottom:8px;
}
.fin-geral-chart-card .fin-chart-box,
.fin-geral-chart-grid .fin-chart-box,
.fin-chart-box-main-compact{
    height:300px !important;
    min-height:300px !important;
    max-height:300px !important;
}
.fin-geral-chart-grid .fin-chart-box.side,
.fin-chart-box-side-compact,
.fin-chart-box-side-tall{
    height:260px !important;
    min-height:260px !important;
    max-height:260px !important;
}
.fin-geral-chart-grid canvas,
.fin-geral-chart-card canvas{
    max-height:none !important;
}
.fin-geral-insights{
    margin-top:8px;
    align-items:stretch;
}
.fin-geral-insights .insight-item{
    min-height:88px;
}
@media (max-width: 1100px){
    .fin-geral-chart-grid{
        grid-template-columns:1fr;
    }
    .fin-geral-chart-card .fin-chart-box,
    .fin-geral-chart-grid .fin-chart-box,
    .fin-chart-box-main-compact{
        height:260px !important;
        min-height:260px !important;
        max-height:260px !important;
    }
    .fin-geral-chart-grid .fin-chart-box.side,
    .fin-chart-box-side-compact,
    .fin-chart-box-side-tall{
        height:220px !important;
        min-height:220px !important;
        max-height:220px !important;
    }
}
@media (max-width: 760px){
    .fin-geral-chart-card .fin-chart-box,
    .fin-geral-chart-grid .fin-chart-box,
    .fin-chart-box-main-compact{
        height:220px !important;
        min-height:220px !important;
        max-height:220px !important;
    }
    .fin-geral-chart-grid .fin-chart-box.side,
    .fin-chart-box-side-compact,
    .fin-chart-box-side-tall{
        height:200px !important;
        min-height:200px !important;
        max-height:200px !important;
    }
}


/* v15 - centralização e explicação completa do gráfico da aba geral */
.fin-geral-chart-grid{
    grid-template-columns:minmax(0,1.08fr) minmax(280px,.38fr);
    gap:18px;
    align-items:center;
}
.fin-chart-box-main-compact{
    display:flex;
    align-items:center;
    justify-content:center;
    height:340px !important;
    min-height:340px !important;
    max-height:340px !important;
    padding:8px 12px 4px;
}
.fin-geral-chart-card .fin-chart-box.main{
    height:340px !important;
    min-height:340px !important;
    max-height:340px !important;
}
.fin-chart-center-wrap{
    width:100%;
    max-width:900px;
    margin:0 auto;
    height:100%;
    display:flex;
    align-items:center;
    justify-content:center;
}
.fin-chart-center-wrap canvas{
    width:100% !important;
    height:100% !important;
    max-height:340px !important;
}
.fin-geral-chart-grid .fin-chart-box.side,
.fin-chart-box-side-compact,
.fin-chart-box-side-tall{
    height:240px !important;
    min-height:240px !important;
    max-height:240px !important;
    align-self:center;
}
.fin-geral-insights{
    margin-top:10px;
}
.fin-geral-insights-4{
    grid-template-columns:1.2fr 1.1fr repeat(2,minmax(0,0.9fr));
}
.fin-geral-insights .insight-item{
    min-height:unset;
}
@media (max-width: 1200px){
    .fin-geral-chart-grid,
    .fin-geral-insights-4{
        grid-template-columns:1fr;
    }
    .fin-chart-box-main-compact,
    .fin-geral-chart-card .fin-chart-box.main{
        height:300px !important;
        min-height:300px !important;
        max-height:300px !important;
    }
    .fin-chart-center-wrap canvas{
        max-height:300px !important;
    }
}
@media (max-width: 760px){
    .fin-chart-box-main-compact,
    .fin-geral-chart-card .fin-chart-box.main{
        height:260px !important;
        min-height:260px !important;
        max-height:260px !important;
    }
    .fin-chart-center-wrap canvas{
        max-height:260px !important;
    }
}


/* v16 - gráfico da aba geral mais à esquerda, maior e sem os dois primeiros cards */
.fin-geral-chart-grid{
    grid-template-columns:minmax(0,1.22fr) minmax(300px,.34fr) !important;
    gap:14px !important;
    align-items:start !important;
}
.fin-chart-box-main-compact{
    align-items:flex-start !important;
    justify-content:flex-start !important;
    height:365px !important;
    min-height:365px !important;
    max-height:365px !important;
    padding:4px 6px 0 0 !important;
}
.fin-geral-chart-card .fin-chart-box.main{
    height:365px !important;
    min-height:365px !important;
    max-height:365px !important;
}
.fin-chart-center-wrap{
    max-width:none !important;
    margin:0 !important;
    align-items:flex-start !important;
    justify-content:flex-start !important;
}
.fin-chart-center-wrap canvas{
    max-height:365px !important;
}
.fin-geral-chart-grid .fin-chart-box.side,
.fin-chart-box-side-compact,
.fin-chart-box-side-tall{
    height:250px !important;
    min-height:250px !important;
    max-height:250px !important;
}
.fin-geral-insights-2{
    grid-template-columns:repeat(2,minmax(0,1fr));
    margin-top:2px !important;
}
@media (max-width: 1200px){
    .fin-geral-chart-grid,
    .fin-geral-insights-2{
        grid-template-columns:1fr !important;
    }
    .fin-chart-box-main-compact,
    .fin-geral-chart-card .fin-chart-box.main{
        height:320px !important;
        min-height:320px !important;
        max-height:320px !important;
    }
    .fin-chart-center-wrap canvas{
        max-height:320px !important;
    }
}
@media (max-width: 760px){
    .fin-chart-box-main-compact,
    .fin-geral-chart-card .fin-chart-box.main{
        height:280px !important;
        min-height:280px !important;
        max-height:280px !important;
    }
    .fin-chart-center-wrap canvas{
        max-height:280px !important;
    }
}

/* v17 - aba geral: menos vazio, gráfico principal mais visível e melhor alinhado */
.fin-geral-chart-grid{
    grid-template-columns:minmax(0,1.28fr) minmax(290px,.32fr) !important;
    gap:12px !important;
    align-items:start !important;
}
.fin-geral-chart-card{
    gap:0 !important;
}
.fin-geral-chart-card .fin-card-head{
    margin-bottom:8px !important;
}
.fin-geral-chart-card .fin-chart-box.main,
.fin-chart-box-main-compact{
    height:300px !important;
    min-height:300px !important;
    max-height:300px !important;
    padding:0 4px 0 0 !important;
    align-items:flex-start !important;
    justify-content:flex-start !important;
}
.fin-chart-center-wrap{
    width:100% !important;
    height:100% !important;
    max-width:none !important;
    margin:0 !important;
    align-items:flex-start !important;
    justify-content:flex-start !important;
}
.fin-chart-center-wrap canvas{
    width:100% !important;
    height:300px !important;
    max-height:300px !important;
}
.fin-geral-chart-grid .fin-chart-box.side,
.fin-chart-box-side-compact,
.fin-chart-box-side-tall{
    height:248px !important;
    min-height:248px !important;
    max-height:248px !important;
    align-self:start !important;
}
.fin-geral-insights-2{
    grid-template-columns:repeat(2,minmax(0,1fr)) !important;
    margin-top:0 !important;
    gap:12px !important;
}
.fin-geral-insights .insight-item{
    min-height:84px !important;
}
@media (max-width: 1200px){
    .fin-geral-chart-grid,
    .fin-geral-insights-2{
        grid-template-columns:1fr !important;
    }
    .fin-geral-chart-card .fin-chart-box.main,
    .fin-chart-box-main-compact,
    .fin-chart-center-wrap canvas{
        height:280px !important;
        min-height:280px !important;
        max-height:280px !important;
    }
    .fin-geral-chart-grid .fin-chart-box.side,
    .fin-chart-box-side-compact,
    .fin-chart-box-side-tall{
        height:220px !important;
        min-height:220px !important;
        max-height:220px !important;
    }
}
@media (max-width: 760px){
    .fin-geral-chart-card .fin-chart-box.main,
    .fin-chart-box-main-compact,
    .fin-chart-center-wrap canvas{
        height:240px !important;
        min-height:240px !important;
        max-height:240px !important;
    }
}


/* v18 - ajuste real da aba geral */
.fin-geral-chart-card{
    overflow:hidden;
}
.fin-geral-chart-grid{
    display:grid !important;
    grid-template-columns:minmax(0,1.7fr) minmax(300px,.55fr) !important;
    gap:16px !important;
    align-items:start !important;
}
.fin-geral-chart-card .fin-chart-box.main,
.fin-chart-box-main-compact{
    height:340px !important;
    min-height:340px !important;
    max-height:340px !important;
    padding:0 !important;
    margin:0 !important;
    display:block !important;
}
.fin-chart-center-wrap{
    width:100% !important;
    height:340px !important;
    max-width:none !important;
    margin:0 !important;
    display:block !important;
}
.fin-chart-center-wrap canvas{
    width:100% !important;
    height:340px !important;
    max-height:340px !important;
}
.fin-geral-chart-grid .fin-chart-box.side,
.fin-chart-box-side-compact,
.fin-chart-box-side-tall{
    height:230px !important;
    min-height:230px !important;
    max-height:230px !important;
    margin-top:12px !important;
}
.fin-geral-insights-2{
    margin-top:8px !important;
    gap:12px !important;
}
.fin-geral-insights .insight-item{
    min-height:auto !important;
}
@media (max-width:1200px){
    .fin-geral-chart-grid{
        grid-template-columns:1fr !important;
    }
    .fin-geral-chart-card .fin-chart-box.main,
    .fin-chart-box-main-compact,
    .fin-chart-center-wrap,
    .fin-chart-center-wrap canvas{
        height:300px !important;
        min-height:300px !important;
        max-height:300px !important;
    }
}
@media (max-width:760px){
    .fin-geral-chart-card .fin-chart-box.main,
    .fin-chart-box-main-compact,
    .fin-chart-center-wrap,
    .fin-chart-center-wrap canvas{
        height:250px !important;
        min-height:250px !important;
        max-height:250px !important;
    }
}


/* v19 - correção final do gráfico da aba geral */
.fin-geral-chart-card{
    display:flex;
    flex-direction:column;
    overflow:visible !important;
}

.fin-geral-chart-grid{
    display:grid !important;
    grid-template-columns:minmax(0,1.08fr) minmax(280px,.38fr) !important;
    gap:18px !important;
    align-items:center !important;
    margin-bottom:8px;
}

.fin-geral-chart-card .fin-chart-box.main,
.fin-chart-box-main-compact{
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    height:340px !important;
    min-height:340px !important;
    max-height:340px !important;
    padding:10px 18px 8px !important;
    overflow:visible !important;
}

.fin-chart-center-wrap{
    width:100% !important;
    max-width:900px !important;
    margin:0 auto !important;
    height:100% !important;
    display:flex !important;
    align-items:center !important;
    justify-content:center !important;
    overflow:visible !important;
}

.fin-chart-center-wrap canvas{
    width:100% !important;
    height:100% !important;
    max-height:340px !important;
}

.fin-geral-chart-grid .fin-chart-box.side,
.fin-chart-box-side-compact,
.fin-chart-box-side-tall{
    height:240px !important;
    min-height:240px !important;
    max-height:240px !important;
    align-self:center !important;
    margin-top:0 !important;
    padding:14px 14px 10px !important;
    border-radius:16px;
    border:1px solid rgba(255,255,255,.06);
    background:linear-gradient(180deg,rgba(255,255,255,.03),rgba(255,255,255,.015));
    overflow:visible !important;
}

.fin-geral-chart-grid canvas,
.fin-geral-chart-card canvas{
    width:100% !important;
    height:100% !important;
    max-height:none !important;
}

.fin-geral-insights,
.fin-geral-insights-2{
    display:grid;
    grid-template-columns:repeat(2,minmax(0,1fr));
    gap:12px;
    margin-top:8px !important;
    align-items:stretch;
}

.fin-geral-insights .insight-item{
    min-height:88px !important;
}

@media (max-width: 1100px){
    .fin-geral-chart-grid{
        grid-template-columns:1fr !important;
    }

    .fin-geral-chart-card .fin-chart-box.main,
    .fin-chart-box-main-compact{
        height:280px !important;
        min-height:280px !important;
        max-height:280px !important;
    }

    .fin-geral-chart-grid .fin-chart-box.side,
    .fin-chart-box-side-compact,
    .fin-chart-box-side-tall{
        height:220px !important;
        min-height:220px !important;
        max-height:220px !important;
    }
}

@media (max-width: 760px){
    .fin-geral-insights,
    .fin-geral-insights-2{
        grid-template-columns:1fr !important;
    }

    .fin-geral-chart-card .fin-chart-box.main,
    .fin-chart-box-main-compact{
        height:240px !important;
        min-height:240px !important;
        max-height:240px !important;
        padding:8px 10px 6px !important;
    }

    .fin-geral-chart-grid .fin-chart-box.side,
    .fin-chart-box-side-compact,
    .fin-chart-box-side-tall{
        height:200px !important;
        min-height:200px !important;
        max-height:200px !important;
    }
}


/* ===== AJUSTES CHATGPT V2 ===== */
.sidebar .nav-link,
.nav-link {
    transition: background-color .18s ease, border-color .18s ease, color .18s ease, transform .18s ease, box-shadow .18s ease;
}
.sidebar .nav-link:hover,
.nav-link:hover {
    background: linear-gradient(180deg, rgba(163,29,30,.96), rgba(132,23,24,.96)) !important;
    border-color: rgba(255,255,255,.14) !important;
    color: #ffffff !important;
    box-shadow: 0 10px 24px rgba(163,29,30,.18);
}
.sidebar .nav-link:hover .ni,
.nav-link:hover .ni {
    color: #ffffff !important;
    background: rgba(255,255,255,.12) !important;
    border-color: rgba(255,255,255,.18) !important;
}
.sidebar .nav-link:hover .nav-meta strong,
.sidebar .nav-link:hover .nav-meta span,
.nav-link:hover .nav-meta strong,
.nav-link:hover .nav-meta span {
    color: #ffffff !important;
}
.page-head-actions .btn-export,
.toolbar-actions .btn-export,
.fin-filter-actions .btn-export,
.factions .btn-export {
    background: linear-gradient(180deg, rgba(22,163,74,.96), rgba(21,128,61,.96));
    color: #fff;
    border: 1px solid rgba(255,255,255,.10);
}
.page-head-actions .btn-export:hover,
.toolbar-actions .btn-export:hover,
.fin-filter-actions .btn-export:hover,
.factions .btn-export:hover {
    filter: brightness(1.03);
    color: #fff;
}
.dist-filters {
    grid-template-columns: 1.6fr repeat(7, minmax(110px, 1fr)) auto;
    gap: 10px;
    padding: 14px 16px;
}
.dist-filters .fg label {
    font-size: 9.5px;
    letter-spacing: .10em;
}
.dist-filters input, .dist-filters select {
    height: 38px;
    padding: 8px 10px;
    font-size: 12.5px;
    border-radius: 10px;
}
.overview-card, .analytics-card {
    background: linear-gradient(180deg, #171c24 0%, #141922 100%);
}
.overview-grid {
    gap: 14px;
    align-items: stretch;
}
.chart-wrap.tall {
    min-height: 255px;
}
.chart-wrap.short {
    min-height: 210px;
}
.analytics-grid {
    gap: 12px;
}
.analytics-card .chart-wrap {
    min-height: 230px;
}
.company-highlight {
    font-weight: 800;
    color: #f5f7fb;
}
.dtbl td, .dtbl th {
    padding-top: 10px;
    padding-bottom: 10px;
    vertical-align: top;
}
.fin-ranking-chart-wrap {
    position: relative;
    min-height: 300px;
    padding: 8px 8px 2px;
    margin-bottom: 12px;
    border-radius: 18px;
    border: 1px solid rgba(255,255,255,.06);
    background: linear-gradient(180deg, rgba(255,255,255,.03), rgba(255,255,255,.015));
}
.fin-ranking-list.compact {
    gap: 10px;
}
.fin-ranking-row.compact {
    padding: 12px 14px;
    border-radius: 16px;
    border: 1px solid rgba(255,255,255,.06);
    background: linear-gradient(180deg, rgba(255,255,255,.028), rgba(255,255,255,.015));
}
.fin-ranking-bar {
    height: 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.06);
    overflow: hidden;
}
.fin-ranking-fill {
    border-radius: 999px;
    background: linear-gradient(90deg, rgba(163,29,30,.95), rgba(220,38,38,.95));
}
@media (max-width: 1320px) {
    .dist-filters {
        grid-template-columns: repeat(4, minmax(150px, 1fr));
    }
    .dist-filters .fg.wide {
        grid-column: 1 / -1;
    }
    .dist-filters .factions {
        grid-column: 1 / -1;
        justify-content: flex-end;
    }
}
@media (max-width: 780px) {
    .dist-filters {
        grid-template-columns: 1fr;
    }
    .analytics-grid,
    .overview-grid {
        grid-template-columns: 1fr;
    }
    .chart-wrap.tall,
    .analytics-card .chart-wrap,
    .fin-ranking-chart-wrap {
        min-height: 240px;
    }
}


/* ===== Ajuste gráfico resumo por empresa ===== */
.fin-ranking-chart-wrap {
    min-height: 430px;
    padding: 14px 16px 10px;
}

.fin-ranking-chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
}

@media (max-width: 1100px) {
    .fin-ranking-chart-wrap {
        min-height: 360px;
    }
}

@media (max-width: 768px) {
    .fin-ranking-chart-wrap {
        min-height: 320px;
        padding: 12px 12px 8px;
    }
}


/* ===== Resumo por empresa v5 ===== */
.fin-resumo-empresa-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.6fr) minmax(300px, 0.9fr);
    gap: 1rem;
    align-items: stretch;
}

.fin-ranking-sidecard {
    height: 100%;
    border-radius: 18px;
    padding: 1rem;
    background: linear-gradient(180deg, rgba(15,23,42,.88), rgba(19,29,48,.92));
    border: 1px solid rgba(255,255,255,.07);
    box-shadow: 0 10px 30px rgba(0,0,0,.18);
}

.fin-ranking-sidecard h6 {
    margin: 0 0 .35rem;
    color: #e5e7eb;
    font-size: 1rem;
    font-weight: 700;
}

.fin-ranking-sidecard p {
    margin: 0 0 1rem;
    color: #9fb0c4;
    font-size: .88rem;
}

.fin-ranking-list {
    display: flex;
    flex-direction: column;
    gap: .7rem;
}

.fin-ranking-item {
    display: grid;
    grid-template-columns: 44px minmax(0,1fr) auto;
    gap: .75rem;
    align-items: center;
    padding: .75rem .8rem;
    border-radius: 14px;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.05);
}

.fin-ranking-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: 999px;
    font-weight: 800;
    font-size: .9rem;
    color: #ffe5e5;
    background: linear-gradient(180deg, rgba(163,29,30,.95), rgba(120,18,19,.95));
    border: 1px solid rgba(255,255,255,.08);
}

.fin-ranking-company {
    min-width: 0;
}

.fin-ranking-company strong {
    display: block;
    color: #f3f4f6;
    font-size: .92rem;
    line-height: 1.25rem;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.fin-ranking-company span {
    display: block;
    margin-top: .15rem;
    color: #93a4ba;
    font-size: .8rem;
}

.fin-ranking-value {
    color: #f9fafb;
    font-weight: 700;
    font-size: .92rem;
    white-space: nowrap;
}

@media (max-width: 1100px) {
    .fin-resumo-empresa-grid {
        grid-template-columns: 1fr;
    }
}


/* ===== v6-ajustes ===== */

/* legenda afastada */
.chartjs-legend ul {
    margin-top: 10px !important;
}

/* espaço interno gráfico evolução */
.evolucao-chart-wrap {
    padding-top: 20px;
}

/* card custo médio */
.card-custo-medio {
    display:flex;
    flex-direction:column;
    justify-content:center;
    padding:16px;
}

/* centralizar gráfico pequeno */
.card-custo-medio canvas {
    margin-top:10px;
}

/* padronização cards */
.dashboard-card {
    border-radius:16px;
    padding:16px;
    background:linear-gradient(180deg, rgba(15,23,42,.9), rgba(20,30,50,.95));
    border:1px solid rgba(255,255,255,.06);
}

.dashboard-card h3 {
    color:#f3f4f6;
    font-weight:700;
}

.dashboard-card span {
    color:#9fb0c4;
}


/* ===== v7-financeiro-ajustes-reais ===== */

/* cards KPI padronizados */
.fin-kpi:before,
.fin-kpi-total:before,
.fin-kpi-aluguel:before,
.fin-kpi-paginas:before,
.fin-kpi-impressoras:before,
.fin-kpi-empresas:before,
.fin-kpi-competencias:before {
    background: linear-gradient(90deg, rgba(163,29,30,.95), rgba(240,86,88,.60)) !important;
}

/* evolução mensal */
.fin-geral-chart-grid {
    grid-template-columns: minmax(0, 1fr) minmax(300px, 0.42fr);
    gap: 18px;
    align-items: stretch;
}

.fin-chart-box-main-compact {
    min-height: 300px !important;
    max-height: 300px !important;
    height: 300px !important;
    padding-top: 6px;
}

.fin-chart-center-wrap {
    align-items: flex-start !important;
}

.fin-chart-center-wrap canvas {
    max-height: 270px !important;
}

/* card custo médio */
.fin-chart-box-side-compact,
.fin-chart-box-side-tall,
.fin-geral-chart-grid .fin-chart-box.side {
    min-height: 300px !important;
    max-height: 300px !important;
    height: 300px !important;
    display: flex;
    flex-direction: column;
    justify-content: flex-start;
}

.fin-chart-mini-head {
    margin-bottom: 6px !important;
}

#finGeralCustoPaginaChart {
    margin-top: 2px !important;
}

/* resumo por empresa */
.fin-ranking-chart-wrap {
    min-height: 340px !important;
    height: 340px !important;
    margin-bottom: 0 !important;
    padding: 12px 14px 6px !important;
}

.fin-ranking-chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
}

.fin-resumo-empresa-grid {
    display: grid;
    grid-template-columns: minmax(0, 1.7fr) minmax(320px, 0.85fr);
    gap: 16px;
    align-items: stretch;
}

.fin-ranking-sidecard {
    min-height: 340px;
}

@media (max-width: 1100px) {
    .fin-resumo-empresa-grid,
    .fin-geral-chart-grid {
        grid-template-columns: 1fr !important;
    }

    .fin-ranking-sidecard,
    .fin-ranking-chart-wrap,
    .fin-chart-box-main-compact,
    .fin-chart-box-side-compact,
    .fin-chart-box-side-tall,
    .fin-geral-chart-grid .fin-chart-box.side {
        min-height: 300px !important;
        height: auto !important;
        max-height: none !important;
    }
}


/* v9-fallback-resumo */
.fin-ranking-chart-wrap { min-height: 340px !important; height: 340px !important; }
.fin-ranking-chart-wrap canvas { width: 100% !important; height: 100% !important; display: block; }


/* ===== v11-resumo-empresa-full ===== */
.fin-resumo-empresa-full {
    display: flex;
    flex-direction: column;
    gap: 14px;
}

.fin-ranking-chart-wrap-full {
    min-height: 440px !important;
    height: 440px !important;
    padding: 14px 14px 4px !important;
}

.fin-resumo-empresa-totais {
    display: grid;
    grid-template-columns: repeat(4, minmax(180px, 1fr));
    gap: 12px;
}

.fin-mini-kpi {
    border-radius: 14px;
    padding: 14px 16px;
    background: linear-gradient(180deg, rgba(15,23,42,.90), rgba(20,30,50,.95));
    border: 1px solid rgba(255,255,255,.06);
}

.fin-mini-kpi .lbl {
    display: block;
    color: #9fb0c4;
    font-size: .82rem;
    margin-bottom: 6px;
}

.fin-mini-kpi strong {
    color: #f3f4f6;
    font-size: 1.05rem;
    font-weight: 700;
}

.fin-resumo-empresa-table .fin-table td strong {
    color: #f3f4f6;
}

@media (max-width: 1100px) {
    .fin-resumo-empresa-totais {
        grid-template-columns: repeat(2, minmax(180px, 1fr));
    }

    .fin-ranking-chart-wrap-full {
        min-height: 380px !important;
        height: 380px !important;
    }
}

@media (max-width: 680px) {
    .fin-resumo-empresa-totais {
        grid-template-columns: 1fr;
    }
}


/* ===== v11_2-ajustes-evolucao ===== */
.fin-chart-box-main-compact,
.fin-chart-box-main,
.fin-geral-chart-grid .fin-chart-box:first-child {
    min-height: 330px !important;
    height: 330px !important;
    padding-top: 12px !important;
}

.fin-geral-chart-grid .fin-chart-box:first-child canvas,
#finGeralEvolucaoChart {
    margin-top: 10px !important;
}

.fin-chart-box-side-compact,
.fin-chart-box-side-tall,
.fin-geral-chart-grid .fin-chart-box.side {
    min-height: 260px !important;
    max-height: 260px !important;
    height: 260px !important;
    align-self: start !important;
}

.fin-chart-box-side-compact canvas,
.fin-chart-box-side-tall canvas,
#finGeralCustoPaginaChart {
    max-height: 170px !important;
}

.fin-chart-mini-head {
    margin-bottom: 2px !important;
}

@media (max-width: 1100px) {
    .fin-chart-box-side-compact,
    .fin-chart-box-side-tall,
    .fin-geral-chart-grid .fin-chart-box.side {
        min-height: 250px !important;
        max-height: none !important;
        height: auto !important;
    }
}


/* ===== v12-inventario-tabs ===== */
.inventory-overview-grid {
    display: grid;
    grid-template-columns: repeat(4, minmax(180px, 1fr));
    gap: 14px;
    margin-bottom: 14px;
}

.inventory-kpi-card {
    border-radius: 18px;
    padding: 16px 18px;
    background: linear-gradient(180deg, rgba(15,23,42,.92), rgba(20,30,50,.96));
    border: 1px solid rgba(255,255,255,.06);
    box-shadow: 0 10px 28px rgba(0,0,0,.14);
}

.inventory-kpi-label {
    display: block;
    color: #9fb0c4;
    font-size: .8rem;
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 8px;
}

.inventory-kpi-card strong {
    display: block;
    color: #f3f4f6;
    font-size: 1.5rem;
    font-weight: 800;
    line-height: 1.1;
}

.inventory-kpi-card small {
    display: block;
    color: #91a3bb;
    margin-top: 6px;
    font-size: .82rem;
}

.inventory-card .toolbar-shell {
    padding: 14px 18px 10px;
    border-bottom: 1px solid var(--bdr);
}

.inventory-table-wrap {
    width: 100%;
    overflow-x: auto;
}

.inventory-table {
    min-width: 980px;
}

.inventory-table td {
    vertical-align: top;
}

.inventory-table .asset-summary {
    min-width: 340px;
}

.inventory-table .asset-name {
    font-size: .98rem;
}

.inventory-table .asset-meta,
.inventory-table .asset-tech {
    gap: .45rem;
}

.inventory-table .asset-pill,
.inventory-table .asset-kv {
    border-radius: 999px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.05);
    padding: .34rem .6rem;
}

.inventory-table .badge {
    white-space: nowrap;
}

@media (max-width: 1100px) {
    .inventory-overview-grid {
        grid-template-columns: repeat(2, minmax(180px, 1fr));
    }
}

@media (max-width: 700px) {
    .inventory-overview-grid {
        grid-template-columns: 1fr;
    }

    .inventory-card .toolbar-shell {
        padding: 12px 14px 10px;
    }

    .inventory-table {
        min-width: 860px;
    }

    .inventory-table .asset-summary {
        min-width: 280px;
    }
}


/* ===== v14-table-space-better ===== */
.inventory-table {
    min-width: 1320px;
    table-layout: fixed;
}

.inventory-table th,
.inventory-table td {
    padding: 12px 14px;
}

.inventory-table .asset-summary-expanded {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 0;
}

.asset-inline-notes {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.asset-inline-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.04);
    border: 1px solid rgba(255,255,255,.06);
    color: #c9d5e3;
    font-size: .82rem;
    line-height: 1.1;
}

.asset-side-block {
    display: grid;
    gap: 8px;
    min-width: 0;
}

.asset-side-row {
    display: grid;
    gap: 2px;
    padding: 8px 10px;
    border-radius: 12px;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.05);
}

.asset-side-row strong,
.asset-side-row span:last-child {
    color: #f3f4f6;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.asset-side-label {
    color: #8ea0b9;
    font-size: .74rem;
    text-transform: uppercase;
    letter-spacing: .05em;
}

.asset-spec-grid {
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 8px;
    min-width: 0;
}

.asset-spec-item {
    min-width: 0;
    padding: 8px 10px;
    border-radius: 12px;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.05);
}

.asset-spec-item span {
    display: block;
    color: #8ea0b9;
    font-size: .74rem;
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: 4px;
}

.asset-spec-item strong {
    display: block;
    color: #f3f4f6;
    font-size: .84rem;
    line-height: 1.25;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.inventory-table td:nth-child(4),
.inventory-table td:nth-child(5),
.inventory-table td:nth-child(6),
.inventory-table th:nth-child(4),
.inventory-table th:nth-child(5),
.inventory-table th:nth-child(6) {
    text-align: center;
}

@media (max-width: 700px) {
    .inventory-table {
        min-width: 1180px;
    }
}


/* ===== v15-premium-layout ===== */
.inventory-table {
    min-width: 1240px;
    table-layout: fixed;
}

.inventory-table td,
.inventory-table th {
    padding: 12px 14px;
    vertical-align: middle;
}

.asset-premium-main {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 0;
}

.asset-premium-title {
    display: flex;
    flex-direction: column;
    gap: 4px;
    min-width: 0;
}

.asset-premium-row {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}

.asset-inline-info {
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-width: 0;
}

.asset-inline-info span {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 0;
    color: #d1d9e6;
    font-size: .86rem;
    line-height: 1.35;
    min-width: 0;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.asset-inline-info svg {
    flex: 0 0 auto;
    opacity: .85;
}

.asset-spec-inline {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    align-items: center;
}

.asset-spec-inline span,
.asset-inline-chip {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 7px 10px;
    border-radius: 999px;
    background: rgba(255,255,255,.045);
    border: 1px solid rgba(255,255,255,.07);
    color: #d8e1ec;
    font-size: .81rem;
    line-height: 1.15;
    max-width: 100%;
}

.asset-spec-inline span {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.asset-premium-main .asset-name {
    font-size: 1.02rem;
}

.inventory-table .badge {
    margin: 0 auto;
}

.inventory-table td:nth-child(4),
.inventory-table td:nth-child(5),
.inventory-table td:nth-child(6),
.inventory-table th:nth-child(4),
.inventory-table th:nth-child(5),
.inventory-table th:nth-child(6) {
    text-align: center;
}

@media (max-width: 700px) {
    .inventory-table {
        min-width: 1120px;
    }
}


/* ===== v15_1-fix-icon-size ===== */
.asset-inline-info svg,
.asset-spec-inline svg,
.asset-inline-chip svg,
.asset-premium-row svg,
.asset-premium-main svg,
.inventory-table .asset-summary svg,
.inventory-table .asset-premium-main svg {
    width: 14px !important;
    height: 14px !important;
    min-width: 14px !important;
    min-height: 14px !important;
    max-width: 14px !important;
    max-height: 14px !important;
    display: inline-block !important;
    vertical-align: middle !important;
    flex: 0 0 14px !important;
}

.asset-inline-info span,
.asset-spec-inline span,
.asset-inline-chip {
    align-items: center !important;
}

.asset-premium-row,
.asset-spec-inline,
.asset-inline-info {
    position: relative;
    z-index: 1;
}


/* ===== v16-dashboard-print-summary ===== */
.kpi-grid{
    grid-template-columns: repeat(5, minmax(0, 1fr));
}
.kpi-card-print .kpi-strip{
    background: linear-gradient(90deg, #0ea5e9, #22c55e);
}

.print-summary-card{
    padding: 18px;
}
.print-summary-grid{
    display: grid;
    grid-template-columns: minmax(340px, 0.95fr) minmax(0, 1.25fr);
    gap: 18px;
    align-items: start;
}
.print-summary-metrics{
    display: grid;
    grid-template-columns: repeat(2, minmax(0, 1fr));
    gap: 12px;
}
.print-summary-kpi{
    border-radius: 16px;
    padding: 16px;
    background: linear-gradient(180deg, rgba(15,23,42,.88), rgba(19,29,48,.92));
    border: 1px solid rgba(255,255,255,.06);
}
.print-summary-kpi .k{
    display:block;
    font-size:11px;
    text-transform:uppercase;
    letter-spacing:.08em;
    color:#95a6bc;
    margin-bottom:8px;
}
.print-summary-kpi strong{
    display:block;
    color:#f4f7fb;
    font-size:22px;
    line-height:1.15;
    letter-spacing:-.03em;
}
.print-summary-kpi small{
    display:block;
    color:#8fa1b7;
    margin-top:7px;
    font-size:12px;
}
.print-company-list{
    display:flex;
    flex-direction:column;
    gap:12px;
}
.print-company-row{
    display:grid;
    grid-template-columns: 42px minmax(0, 1fr);
    gap: 12px;
    align-items: start;
}
.print-company-rank{
    width:42px;
    height:42px;
    border-radius:999px;
    display:flex;
    align-items:center;
    justify-content:center;
    font-weight:800;
    color:#fff;
    background: linear-gradient(180deg, rgba(163,29,30,.95), rgba(122,22,23,.95));
    border:1px solid rgba(255,255,255,.08);
}
.print-company-main{
    min-width:0;
    border-radius:16px;
    padding:14px 15px;
    background: rgba(255,255,255,.03);
    border: 1px solid rgba(255,255,255,.06);
}
.print-company-top{
    display:flex;
    justify-content:space-between;
    gap:12px;
    align-items:center;
    margin-bottom:8px;
}
.print-company-name{
    min-width:0;
    color:#eef2f7;
    font-weight:700;
    white-space:nowrap;
    overflow:hidden;
    text-overflow:ellipsis;
}
.print-company-total{
    color:#f4f7fb;
    font-weight:800;
    white-space:nowrap;
}
.print-company-sub{
    margin-top:8px;
    display:flex;
    flex-wrap:wrap;
    gap:10px 14px;
    color:#90a1b7;
    font-size:12px;
}
@media (max-width: 1280px){
    .kpi-grid{ grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .print-summary-grid{ grid-template-columns: 1fr; }
}
@media (max-width: 780px){
    .kpi-grid{ grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .print-summary-metrics{ grid-template-columns: 1fr; }
}


/* ===== v16.8-filtros-select-dark ===== */
select,
.toolbar-select,
.dist-filters select,
.fin-filters select,
.toolbar-field select,
.base-field select {
    color-scheme: dark;
}

select option,
select optgroup,
.toolbar-select option,
.toolbar-select optgroup,
.dist-filters select option,
.dist-filters select optgroup,
.fin-filters select option,
.fin-filters select optgroup,
.toolbar-field select option,
.toolbar-field select optgroup,
.base-field select option,
.base-field select optgroup {
    background: #12171f !important;
    color: #eef2f8 !important;
}

select:focus,
.toolbar-select:focus,
.dist-filters select:focus,
.fin-filters select:focus,
.toolbar-field select:focus,
.base-field select:focus {
    background: #151b24 !important;
    color: #eef2f8 !important;
}


/* ===== v9-financeiro-filtros-uma-linha ===== */
.fin-filters{
    grid-template-columns:minmax(150px,.95fr) minmax(110px,.62fr) minmax(140px,.78fr) minmax(160px,.92fr) minmax(120px,.62fr) auto;
    gap:10px;
}
.fin-filters-card{padding:16px 18px;}
.fin-filters .form-group{gap:6px;min-width:0;}
.fin-filters label{font-size:10px;letter-spacing:.07em;}
.fin-filters select,
.fin-filters input{
    min-height:40px;
    height:40px;
    padding:9px 12px;
    font-size:13px;
}
.fin-filter-actions{gap:8px;flex-wrap:nowrap;}
.fin-filter-actions .btn{
    min-height:40px;
    height:40px;
    padding:0 14px;
    font-size:13px;
    white-space:nowrap;
}
.fin-note{
    margin-top:12px;
    padding:12px 14px;
    font-size:13px;
}
@media (max-width:1500px){
    .fin-filters{
        grid-template-columns:minmax(140px,.9fr) minmax(105px,.56fr) minmax(130px,.78fr) minmax(150px,.9fr) minmax(115px,.62fr) auto;
        gap:8px;
    }
    .fin-filter-actions .btn{padding:0 12px;}
}
@media (max-width:1280px){
    .fin-filters{grid-template-columns:1fr 1fr 1fr;}
    .fin-filter-actions{grid-column:1 / -1;justify-content:flex-start;}
}
