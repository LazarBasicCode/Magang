<!DOCTYPE html>

<html lang="id">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0" name="viewport" />
    <link href="https://fonts.googleapis.com/css2?family=Public+Sans:wght@400;500;600;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <title>Kemahasiswaan &middot; SIDA</title>
    <style>
        /* ==========================================================
           1. TOKENS (design variables)
           ========================================================== */
        :root {
            /* base surfaces */
            --canvas: #eef0f7;
            --card: #ffffff;
            --border: #dfe3f1;
            --border-strong: #ced5ea;

            /* text */
            --ink: #2f3349;
            --ink-muted: #6b7280;
            --ink-faint: #9aa1b5;

            /* brand */
            --primary: #5b5fef;
            --primary-dark: #4547d1;
            --primary-soft: #e2e2fd;
            --primary-border: #b9baf7;

            /* status accents (badges) — darker text + tinted bg for real contrast */
            --success: #1a8a3d;
            --success-bg: #d6f5df;
            --success-border: #9fe3b4;

            --info: #0680a3;
            --info-bg: #cdf2fa;
            --info-border: #82dcef;

            --warning: #a15c00;
            --warning-bg: #ffe6b8;
            --warning-border: #ffcb70;

            --danger: #c62f14;
            --danger-bg: #ffd9d0;
            --danger-border: #ffab97;

            --neutral: #44506b;
            --neutral-bg: #e3e6f0;
            --neutral-border: #c3c9dc;

            /* shadows */
            --shadow-card: 0 1px 3px rgba(88, 105, 146, 0.13), 0 1px 2px rgba(57, 72, 110, 0.06);
            --shadow-btn: 0 3px 8px rgba(91, 95, 239, 0.38);

            /* layout */
            --sidebar-w: 260px;
            --header-h: 64px;
            --radius: 10px;
            --radius-lg: 14px;
        }

        /* ==========================================================
            DARK MODE OVERRIDES
            ========================================================== */
        body.dark-mode {
            --canvas: #150f2a;
            --card: #1e293b;
            --border: #334155;
            --border-strong: #475569;

            /* Text */
            --ink: #f8fafc;
            --ink-muted: #94a3b8;
            --ink-faint: #64748b;

            /* Brand / Primary Adjustments */
            --primary-soft: #1e1b4b;
            --primary-border: #3730a3;

            /* Shadow */
            --shadow-card: 0 4px 12px rgba(0, 0, 0, 0.4);
        }

        /* Penyesuaian khusus elemen tertentu saat Dark Mode */
        body.dark-mode .header-capsule {
            background: rgba(30, 41, 59, 0.8);
        }

        body.dark-mode .data-table thead tr {
            background: #182234;
        }

        body.dark-mode .dropdown-panel {
            background: var(--card);
        }

        /* ==========================================================
           2. RESET / BASE
           ========================================================== */
        * {
            box-sizing: border-box;
        }

        html,
        body {
            margin: 0;
            padding: 0;
        }

        body {
            font-family: "Public Sans", sans-serif;
            background: var(--canvas);
            color: var(--ink);
            font-size: 13.5px;
            line-height: 1.45;
            -webkit-font-smoothing: antialiased;
            overscroll-behavior: none;
        }

        a {
            text-decoration: none;
            color: inherit;
        }

        button {
            font-family: inherit;
            cursor: pointer;
        }

        ::-webkit-scrollbar {
            width: 7px;
            height: 7px;
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-strong);
            border-radius: 999px;
        }

        ::-webkit-scrollbar-track {
            background: transparent;
        }

        .material-symbols-outlined {
            font-variation-settings: 'FILL' 0, 'wght' 500, 'GRAD' 0, 'opsz' 24;
            line-height: 1;
        }

        /* ==========================================================
           3. LAYOUT SHELL
           ========================================================== */
        .app-sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: var(--sidebar-w);
            background: var(--card);
            border-right: 1px solid var(--border);
            z-index: 50;
            display: flex;
            flex-direction: column;
        }

        .app-content {
            margin-left: var(--sidebar-w);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .app-header {
            position: fixed;
            top: 0;
            left: var(--sidebar-w);
            right: 0;
            z-index: 40;
            height: var(--header-h);
            display: flex;
            align-items: center;
            padding: 0 24px;
        }

        .header-capsule {
            width: 100%;
            height: 48px;
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid var(--border);
            border-radius: 16px;
            box-shadow: 0 4px 16px rgba(31, 41, 66, 0.08);
            padding: 0 16px;
        }

        .app-main {
            flex: 1;
            padding: 24px;
            padding-top: calc(var(--header-h) + 16px);
        }

        .page-wrap {
            display: flex;
            flex-direction: column;
            gap: 20px;
            max-width: 1600px;
        }

        /* ==========================================================
           4. SIDEBAR
           ========================================================== */
        .sidebar-brand {
            height: var(--header-h);
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 0 24px;
            border-bottom: 1px solid var(--border);
            flex-shrink: 0;
        }

        .sidebar-brand img {
            height: 32px;
            width: 32px;
            object-fit: contain;
            border-radius: 6px;
        }

        .sidebar-brand-text {
            display: flex;
            flex-direction: column;
            line-height: 1.1;
        }

        .sidebar-brand-title {
            font-size: 16px;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -0.01em;
        }

        .sidebar-brand-sub {
            font-size: 11px;
            color: var(--ink-muted);
            margin-top: 2px;
        }

        .sidebar-nav {
            flex: 1;
            overflow-y: auto;
            padding: 12px 16px 24px;
        }

        .nav-group {
            display: flex;
            flex-direction: column;
            gap: 3px;
        }

        .nav-link {
            position: relative;
            display: flex;
            align-items: center;
            gap: 12px;
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            color: var(--ink-muted);
            font-size: 13.5px;
            font-weight: 500;
            transition: background-color .15s ease, color .15s ease;
        }

        .nav-link:hover {
            background: var(--canvas);
            color: var(--ink);
        }

        .nav-link .material-symbols-outlined {
            font-size: 19px;
        }

        .nav-link.is-active {
            background: var(--primary-soft);
            color: var(--primary);
            font-weight: 700;
            border: 1px solid var(--primary-border);
        }

        .nav-link.is-active::before {
            content: "";
            position: absolute;
            left: -16px;
            top: 50%;
            transform: translateY(-50%);
            height: 22px;
            width: 4px;
            border-radius: 0 4px 4px 0;
            background: var(--primary);
        }

        .nav-heading {
            padding: 18px 12px 6px;
            font-size: 10.5px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: var(--ink-faint);
        }

        .sidebar-help {
            flex-shrink: 0;
        }

        .help-card {
            background: var(--canvas);
            border: 1px solid var(--border);
            padding: 0px 12px;
            align-items: center;
            display: flex;
            flex-direction: column;
            gap: 8px;
        }

        .help-card-text {
            font-size: 11.5px;
            color: var(--ink-muted);
            line-height: 1.5;
        }

        /* ==========================================================
           5. HEADER
           ========================================================== */
        .header-inner {
            height: 100%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .header-crumb {
            display: none;
            align-items: center;
            gap: 6px;
            font-size: 12.5px;
            color: var(--ink-muted);
        }

        @media (min-width: 768px) {
            .header-crumb {
                display: flex;
            }
        }

        .header-crumb .link:hover {
            color: var(--primary);
            cursor: pointer;
        }

        .header-crumb .current {
            color: var(--ink);
            font-weight: 700;
        }

        .header-search {
            flex: 1;
            max-width: 420px;
            position: relative;
        }

        .header-search input {
            width: 100%;
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--border);
            background: var(--canvas);
            padding: 0 56px 0 38px;
            font-size: 13px;
            color: var(--ink);
        }

        .header-search input:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft);
        }

        .header-search input::placeholder {
            color: var(--ink-faint);
        }

        .header-search .icon-search {
            position: absolute;
            left: 11px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-faint);
            font-size: 19px;
        }

        .header-search kbd {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 10.5px;
            font-weight: 700;
            color: var(--ink-muted);
            background: var(--card);
            border: 1px solid var(--border-strong);
            border-radius: 5px;
            padding: 2px 6px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 4px;
            flex-shrink: 0;
        }

        .icon-btn {
            position: relative;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 8px;
            border: none;
            background: transparent;
            color: var(--ink-muted);
            transition: background-color .15s ease;
        }

        .icon-btn:hover {
            background: var(--canvas);
        }

        .icon-btn .material-symbols-outlined {
            font-size: 21px;
        }

        .icon-btn .dot {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 7px;
            height: 7px;
            border-radius: 50%;
            background: var(--danger);
            border: 2px solid var(--card);
        }

        .header-divider {
            width: 1px;
            height: 26px;
            background: var(--border);
            margin: 0 6px;
        }

        .header-profile {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 6px 10px 6px 6px;
            border-radius: 8px;
            transition: background-color .15s ease;
        }

        .header-profile:hover {
            background: var(--canvas);
        }

        .header-profile img {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid var(--primary-border);
        }

        .header-profile-text {
            display: none;
            flex-direction: column;
            line-height: 1.25;
        }

        @media (min-width: 640px) {
            .header-profile-text {
                display: flex;
            }
        }

        .header-profile-name {
            font-size: 12.5px;
            font-weight: 700;
            color: var(--ink);
        }

        .header-profile-role {
            font-size: 11px;
            color: var(--ink-muted);
        }

        /* ==========================================================
           6. PAGE TITLE BAR
           ========================================================== */
        .title-bar {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
        }

        .breadcrumb {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            color: var(--ink-muted);
        }

        .breadcrumb .current {
            color: var(--ink);
            font-weight: 600;
        }

        .breadcrumb .material-symbols-outlined {
            font-size: 14px;
        }

        .page-title {
            font-size: 22px;
            font-weight: 800;
            color: var(--ink);
            letter-spacing: -0.01em;
            margin: 2px 0 2px;
        }

        .page-subtitle {
            font-size: 13px;
            color: var(--ink-muted);
        }

        .btn-primary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            height: 44px;
            padding: 0 20px;
            border-radius: var(--radius);
            border: 1px solid var(--primary-dark);
            background: var(--primary);
            color: #fff;
            font-size: 13.5px;
            font-weight: 700;
            box-shadow: var(--shadow-btn);
            transition: background-color .15s ease, transform .1s ease;
            flex-shrink: 0;
        }

        .btn-primary:hover {
            background: var(--primary-dark);
        }

        .btn-primary:active {
            transform: scale(0.98);
        }

        .btn-primary .material-symbols-outlined {
            font-size: 19px;
        }

        /* ==========================================================
           7. STAT CARDS
           ========================================================== */
        .stat-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        @media (min-width: 1024px) {
            .stat-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        .stat-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            padding: 18px 20px;
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 12px;
        }

        .stat-info {
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .stat-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-muted);
        }

        .stat-value {
            font-size: 23px;
            font-weight: 800;
            color: var(--ink);
        }

        .stat-delta {
            font-size: 11.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 3px;
        }

        .stat-delta .material-symbols-outlined {
            font-size: 13px;
        }

        .stat-delta.up {
            color: var(--success);
        }

        .stat-delta.neutral {
            color: var(--ink-muted);
        }

        .stat-icon {
            width: 44px;
            height: 44px;
            border-radius: var(--radius);
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
        }

        .stat-icon .material-symbols-outlined {
            font-size: 22px;
        }

        .stat-icon.primary {
            background: var(--primary-soft);
            color: var(--primary);
            border: 1px solid var(--primary-border);
        }

        .stat-icon.warning {
            background: var(--warning-bg);
            color: var(--warning);
            border: 1px solid var(--warning-border);
        }

        .stat-icon.info {
            background: var(--info-bg);
            color: var(--info);
            border: 1px solid var(--info-border);
        }

        .stat-icon.success {
            background: var(--success-bg);
            color: var(--success);
            border: 1px solid var(--success-border);
        }

        /* ==========================================================
           8. FILTER BAR
           ========================================================== */
        .filter-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            padding: 20px;
        }

        .filter-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 16px;
            align-items: end;
        }

        @media (min-width: 640px) {
            .filter-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        @media (min-width: 1024px) {
            .filter-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }

        @media (min-width: 1280px) {
            .filter-grid {
                grid-template-columns: repeat(5, 1fr);
            }
        }

        .field {
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .field-label {
            font-size: 12px;
            font-weight: 600;
            color: var(--ink-muted);
        }

        .field-control {
            position: relative;
        }

        .field-control select,
        .field-control input[type="text"] {
            width: 100%;
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--border-strong);
            background: var(--card);
            color: var(--ink);
            font-size: 13px;
            padding: 0 12px;
            appearance: none;
        }

        .field-control input[type="text"] {
            padding-left: 36px;
        }

        .field-control select {
            padding-right: 34px;
            cursor: pointer;
        }

        .field-control select:focus,
        .field-control input[type="text"]:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft);
        }

        .field-control .caret {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            pointer-events: none;
            color: var(--ink-muted);
            font-size: 19px;
        }

        .field-control .icon-search {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--ink-faint);
            font-size: 18px;
        }

        /* ---- Custom dropdown (replaces native <select>) ---- */
        .dropdown {
            position: relative;
        }

        .dropdown-trigger {
            width: 100%;
            height: 40px;
            border-radius: 8px;
            border: 1px solid var(--border-strong);
            background: var(--card);
            color: var(--ink);
            font-size: 13px;
            font-weight: 500;
            padding: 0 12px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 8px;
            text-align: left;
            transition: border-color .15s ease, box-shadow .15s ease;
        }

        .dropdown-trigger:hover {
            border-color: var(--primary-border);
        }

        .dropdown-trigger .caret {
            position: static;
            transform: none;
            transition: transform .15s ease;
            font-size: 19px;
            color: var(--ink-muted);
            flex-shrink: 0;
        }

        .dropdown-value {
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .dropdown.is-open .dropdown-trigger {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px var(--primary-soft);
        }

        .dropdown.is-open .dropdown-trigger .caret {
            transform: rotate(180deg);
            color: var(--primary);
        }

        .dropdown-panel {
            position: absolute;
            top: calc(100% + 6px);
            left: 0;
            right: 0;
            z-index: 30;
            background: var(--card);
            border: 1px solid var(--border-strong);
            border-radius: 10px;
            box-shadow: 0 12px 28px rgba(31, 41, 66, 0.14);
            padding: 6px;
            display: none;
            flex-direction: column;
            gap: 2px;
            max-height: 240px;
            overflow-y: auto;
        }

        .dropdown.is-open .dropdown-panel {
            display: flex;
        }

        .dropdown-option {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 9px 10px;
            border-radius: 7px;
            border: none;
            background: transparent;
            color: var(--ink);
            font-size: 13px;
            font-weight: 500;
            text-align: left;
            width: 100%;
            transition: background-color .12s ease, color .12s ease;
        }

        .dropdown-option:hover {
            background: var(--canvas);
        }

        .dropdown-option.is-selected {
            background: var(--primary-soft);
            color: var(--primary-dark);
            font-weight: 700;
        }

        .dropdown-option.is-selected::after {
            content: "check";
            font-family: "Material Symbols Outlined";
            margin-left: auto;
            font-size: 17px;
            color: var(--primary);
        }

        .field-locked {
            height: 40px;
            padding: 0 12px;
            border-radius: 8px;
            border: 1px solid var(--primary-border);
            background: var(--primary-soft);
            display: flex;
            align-items: center;
            justify-content: space-between;
            cursor: not-allowed;
        }

        .field-locked-inner {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .field-locked .material-symbols-outlined {
            font-size: 16px;
            color: var(--primary);
        }

        .field-locked-value {
            font-size: 13px;
            font-weight: 700;
            color: var(--primary);
        }

        .field-search-wide {
            grid-column: 1 / -1;
        }

        @media (min-width: 1280px) {
            .field-search-wide {
                grid-column: auto;
            }
        }

        .filter-actions {
            display: flex;
            align-items: center;
            justify-content: flex-end;
            gap: 10px;
            margin-top: 16px;
            padding-top: 16px;
            border-top: 1px solid var(--border);
        }

        .btn-ghost {
            height: 40px;
            padding: 0 16px;
            border-radius: 8px;
            border: 1px solid var(--border-strong);
            background: var(--card);
            color: var(--ink-muted);
            font-size: 13px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 6px;
            transition: background-color .15s ease, color .15s ease;
        }

        .btn-ghost:hover {
            background: var(--canvas);
            color: var(--ink);
        }

        .btn-ghost .material-symbols-outlined {
            font-size: 17px;
        }

        .btn-apply {
            height: 40px;
            padding: 0 20px;
            border-radius: 8px;
            border: 1px solid var(--primary-dark);
            background: var(--primary);
            color: #fff;
            font-size: 13px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 6px;
            box-shadow: var(--shadow-btn);
            transition: background-color .15s ease;
        }

        .btn-apply:hover {
            background: var(--primary-dark);
        }

        .btn-apply .material-symbols-outlined {
            font-size: 17px;
        }

        /* ==========================================================
           9. TABLE CARD
           ========================================================== */
        .table-card {
            background: var(--card);
            border: 1px solid var(--border);
            border-radius: var(--radius-lg);
            box-shadow: var(--shadow-card);
            overflow: hidden;
        }

        .table-card-header {
            padding: 20px 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-bottom: 1px solid var(--border);
        }

        .table-card-title {
            font-size: 15.5px;
            font-weight: 800;
            color: var(--ink);
        }

        .table-card-subtitle {
            font-size: 12.5px;
            color: var(--ink-muted);
            margin-top: 2px;
        }

        .table-card-tools {
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .tool-btn {
            height: 36px;
            padding: 0 12px;
            border-radius: 8px;
            border: 1px solid var(--border-strong);
            background: var(--canvas);
            color: var(--ink-muted);
            font-size: 12.5px;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background-color .15s ease;
        }

        .tool-btn:hover {
            background: var(--border);
            color: var(--ink);
        }

        .tool-btn .material-symbols-outlined {
            font-size: 16px;
        }

        .table-scroll {
            overflow-x: auto;
        }

        table.data-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            white-space: nowrap;
        }

        .data-table thead tr {
            background: var(--canvas);
            border-bottom: 1px solid var(--border-strong);
        }

        .data-table th {
            padding: 13px 16px;
            font-size: 11px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: .05em;
            color: var(--ink-muted);
        }

        .data-table th:first-child {
            padding-left: 24px;
        }

        .data-table th:last-child {
            padding-right: 24px;
        }

        .data-table th.center {
            text-align: center;
        }

        .data-table tbody tr {
            border-bottom: 1px solid var(--border);
            transition: background-color .12s ease;
        }

        .data-table tbody tr:last-child {
            border-bottom: none;
        }

        .data-table tbody tr:hover {
            background: var(--canvas);
        }

        .data-table td {
            padding: 14px 16px;
            font-size: 13px;
            color: var(--ink);
            vertical-align: middle;
        }

        .data-table td:first-child {
            padding-left: 24px;
        }

        .data-table td:last-child {
            padding-right: 24px;
        }

        .data-table td.center {
            text-align: center;
        }

        .nim-code {
            font-weight: 700;
            color: var(--primary);
            font-size: 12.5px;
        }

        .student-cell {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .avatar {
            width: 36px;
            height: 36px;
            border-radius: 50%;
            flex-shrink: 0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 800;
            font-size: 12.5px;
            border: 1px solid transparent;
        }

        .avatar.c-primary {
            background: var(--primary-soft);
            color: var(--primary);
            border-color: var(--primary-border);
        }

        .avatar.c-info {
            background: var(--info-bg);
            color: var(--info);
            border-color: var(--info-border);
        }

        .avatar.c-warning {
            background: var(--warning-bg);
            color: var(--warning);
            border-color: var(--warning-border);
        }

        .avatar.c-success {
            background: var(--success-bg);
            color: var(--success);
            border-color: var(--success-border);
        }

        .avatar.c-danger {
            background: var(--danger-bg);
            color: var(--danger);
            border-color: var(--danger-border);
        }

        .student-name {
            display: flex;
            flex-direction: column;
        }

        .student-name .name {
            font-weight: 700;
            color: var(--ink);
            font-size: 13px;
        }

        .student-name .prodi {
            font-size: 11.5px;
            color: var(--ink-muted);
            margin-top: 1px;
        }

        .activity-title {
            font-size: 13px;
            color: var(--ink);
            display: block;
            max-width: 260px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .badge {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            padding: 5px 10px;
            border-radius: 6px;
            font-size: 11px;
            font-weight: 800;
            border: 1px solid transparent;
            white-space: nowrap;
        }

        .badge .material-symbols-outlined {
            font-size: 12px;
        }

        .badge-primary {
            background: var(--primary-soft);
            color: var(--primary-dark);
            border-color: var(--primary-border);
        }

        .badge-info {
            background: var(--info-bg);
            color: var(--info);
            border-color: var(--info-border);
        }

        .badge-neutral {
            background: var(--neutral-bg);
            color: var(--neutral);
            border-color: var(--neutral-border);
        }

        .badge-success {
            background: var(--success-bg);
            color: var(--success);
            border-color: var(--success-border);
        }

        .badge-warning {
            background: var(--warning-bg);
            color: var(--warning);
            border-color: var(--warning-border);
        }

        .badge-danger {
            background: var(--danger-bg);
            color: var(--danger);
            border-color: var(--danger-border);
        }

        .year-chip {
            font-size: 12px;
            font-weight: 700;
            color: var(--ink-muted);
        }

        .evidence-link {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 7px 12px;
            border-radius: 8px;
            border: 1px solid var(--border-strong);
            background: var(--canvas);
            color: var(--ink);
            font-size: 11.5px;
            font-weight: 700;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease;
        }

        .evidence-link:hover {
            background: var(--primary);
            border-color: var(--primary-dark);
            color: #fff;
        }

        .evidence-link .material-symbols-outlined {
            font-size: 15px;
        }

        .row-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 4px;
        }

        .row-action-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid transparent;
            background: transparent;
            color: var(--ink-muted);
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color .15s ease, color .15s ease, border-color .15s ease;
        }

        .row-action-btn:hover {
            background: var(--primary-soft);
            border-color: var(--primary-border);
            color: var(--primary);
        }

        .row-action-btn.is-secondary:hover {
            background: var(--canvas);
            border-color: var(--border-strong);
            color: var(--ink);
        }

        .row-action-btn .material-symbols-outlined {
            font-size: 18px;
        }

        /* ==========================================================
           10. TABLE FOOTER / PAGINATION
           ========================================================== */
        .table-footer {
            padding: 16px 24px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            border-top: 1px solid var(--border);
            background: var(--canvas);
        }

        .footer-summary {
            font-size: 12.5px;
            color: var(--ink-muted);
        }

        .footer-summary strong {
            color: var(--ink);
            font-weight: 700;
        }

        .footer-summary .highlight {
            color: var(--primary);
            font-weight: 700;
        }

        .pagination {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .page-btn {
            width: 32px;
            height: 32px;
            border-radius: 8px;
            border: 1px solid var(--border-strong);
            background: var(--card);
            color: var(--ink-muted);
            font-size: 12.5px;
            font-weight: 700;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: background-color .15s ease, color .15s ease;
        }

        .page-btn:hover {
            background: var(--canvas);
            color: var(--ink);
        }

        .page-btn.is-active {
            background: var(--primary);
            border-color: var(--primary-dark);
            color: #fff;
            box-shadow: var(--shadow-btn);
        }

        .page-btn:disabled {
            opacity: .4;
            cursor: not-allowed;
        }

        .page-btn:disabled:hover {
            background: var(--card);
        }

        .page-ellipsis {
            padding: 0 4px;
            color: var(--ink-faint);
            font-size: 12.5px;
        }

        .page-btn .material-symbols-outlined {
            font-size: 18px;
        }

        /* ========================= ASIDE CLOSE ================================ */
        /* Tombol Toggle & Close Default (Sembunyi di Layar Lebar) */
        .sidebar-toggle-btn,
        .sidebar-close-btn {
            display: none;
        }

        .sidebar-overlay {
            position: fixed;
            top: 0;
            left: 0;
            width: 100vw;
            height: 100vh;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(4px);
            -webkit-backdrop-filter: blur(4px);
            z-index: 45;
            opacity: 0;
            visibility: hidden;
            transition: opacity 0.3s ease, visibility 0.3s ease;
        }

        .sidebar-overlay.is-active {
            opacity: 1;
            visibility: visible;
        }

        /* ==========================================================
            RESPONSIVE BREAKPOINT (Layar < 1024px)
            ========================================================== */
        @media (max-width: 1023px) {

            /* Sembunyikan Sidebar ke luar layar kiri */
            .app-sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
                box-shadow: 4px 0 24px rgba(0, 0, 0, 0.15);
            }

            /* Tampilkan Sidebar saat status open */
            .app-sidebar.is-open {
                transform: translateX(0);
            }

            /* Penyesuaian Main Content & Header agar memenuhi layar */
            .app-content {
                margin-left: 0;
            }

            .app-header {
                left: 0;
            }

            /* Tampilkan Tombol Hamburger di Navbar */
            .sidebar-toggle-btn {
                display: flex;
                margin-right: 8px;
            }

            /* Tampilkan Tombol Close di Sidebar Header */
            .sidebar-brand {
                justify-content: space-between;
            }

            .sidebar-close-btn {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 32px;
                height: 32px;
                border-radius: 8px;
                border: none;
                background: var(--canvas);
                color: var(--ink-muted);
            }
        }
    </style>
</head>

<body>
    <!-- ============ SIDEBAR ============ -->
    <aside class="app-sidebar">
        <div class="sidebar-brand">
            <img alt="Logo Institut Asia Malang"
                src="https://lh3.googleusercontent.com/aida-public/AB6AXuA1NafrqE7zgk-MH1bALr-Reu0A8mdjdxELfqfal7zRbOhhfEIbOmwIbrIyTQ764kiX0m5p2hWwUHXmKm2zaoFulJno38GSAJ5DhTUwy5_WMdCi720dka9D3yD_wuZ4wopDiMy_BjOoGK54bVjLP0NiywfI7nL86YI3HsKPXmFlj6hlF4BI5Q8DjXt2aNUOYoU8edBrCcGb0bvA9InhKCQe5cw8H4DHhon4G7_Ydrd9AwmAQnrtYnFjTg" />
            <div class="sidebar-brand-text">
                <span class="sidebar-brand-title">SIDA</span>
                <span class="sidebar-brand-sub">Institut Asia Malang</span>
            </div>
            <button type="button" class="sidebar-close-btn" id="sidebarCloseBtn" aria-label="Tutup Menu">
                <span class="material-symbols-outlined">close</span>
            </button>
        </div>

        <nav class="sidebar-nav">
            <div class="nav-group">
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">dashboard</span>
                    <span>Dashboard</span>
                </a>

                <a href="#" aria-current="page" class="nav-link is-active">
                    <span class="material-symbols-outlined">school</span>
                    <span>Kemahasiswaan</span>
                </a>

                <div class="nav-heading">LPPM</div>
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">person</span>
                    <span>Mahasiswa</span>
                </a>
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">co_present</span>
                    <span>Dosen</span>
                </a>
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">workspace_premium</span>
                    <span>Rekognisi</span>
                </a>

                <div class="nav-heading">Kemitraan</div>
                <a href="#" class="nav-link">
                    <span class="material-symbols-outlined">handshake</span>
                    <span>Kerja Sama</span>
                </a>
            </div>
        </nav>

        <div class="sidebar-help">
            <div class="help-card">
                <p class="help-card-text">© Prodi IT - Institut Asia Malang</p>
            </div>
        </div>
    </aside>

    <!-- ============ MAIN ============ -->
    <div class="app-content">
        <header class="app-header">
            <div class="header-capsule">
                <div class="header-inner">
                    <button type="button" class="icon-btn sidebar-toggle-btn" id="sidebarToggleBtn" aria-label="Buka Menu">
                        <span class="material-symbols-outlined">menu</span>
                    </button>
                    <div class="header-crumb">
                        <span class="link">Kemahasiswaan</span>
                        <span>/</span>
                        <span class="current">Data Prestasi &amp; Kegiatan</span>
                    </div>
                    <div class="header-actions">
                        <button type="button" class="icon-btn" aria-label="Notifikasi">
                            <span class="material-symbols-outlined">notifications</span>
                            <span class="dot"></span>
                        </button>
                        <button type="button" class="icon-btn" id="themeToggleBtn" aria-label="Ganti Tema">
                            <span class="material-symbols-outlined" id="themeIcon">dark_mode</span>
                        </button>
                        <div class="header-divider"></div>
                        <div class="header-profile">
                            <img alt="Profile"
                                src="https://lh3.googleusercontent.com/aida-public/AB6AXuCLig7aONgBDjPPsYrnmTXQraRAlwmODcgdKdw1M52sNCLp0M5ScX4sxlYBkPEuFS3htaKkomlSL-y2DvptVFXLJ-ZvyAdi8SRnje9CKQzhf0DpEz4qDCj5aU0CT-Y7uSAfBfp7qVTOwZhDnnis_7VzlM3IN_ZaQ7bR0H4APRvjJ8XgOrCoKNGAwLA1e71Fbc7cZjbozw0HpzkwnEBqr2RnT2nSKlcrlanlK1Tay9cHe62Ct3yQHxk80Q" />
                            <div class="header-profile-text">
                                <span class="header-profile-name">Admin Kemahasiswaan</span>
                                <span class="header-profile-role">Institut Asia Malang</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <main class="app-main">
            <div class="page-wrap">

                <!-- PAGE TITLE + ACTION -->
                <div class="title-bar">
                    <div>
                        <div class="breadcrumb">
                            <span>Kemahasiswaan</span>
                            <span class="material-symbols-outlined">chevron_right</span>
                            <span class="current">Data Prestasi &amp; Kegiatan</span>
                        </div>
                        <h1 class="page-title">Prestasi &amp; Kegiatan Mahasiswa</h1>
                        <p class="page-subtitle">Pendataan kegiatan akademik, non-akademik, inbis, dan kompetisi
                            &middot; Tahun Akademik 2025/2026 (Genap)</p>
                    </div>
                    <button type="button" class="btn-primary">
                        <span class="material-symbols-outlined">add</span>
                        <span>Tambah Kegiatan</span>
                    </button>
                </div>

                <!-- SUMMARY STAT CARDS -->
                <div class="stat-grid">
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Total Kegiatan</span>
                            <span class="stat-value">48</span>
                            <span class="stat-delta up">
                                <span class="material-symbols-outlined">arrow_upward</span>12% bulan ini
                            </span>
                        </div>
                        <div class="stat-icon primary">
                            <span class="material-symbols-outlined">emoji_events</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Tingkat Nasional</span>
                            <span class="stat-value">27</span>
                            <span class="stat-delta neutral">56% dari total</span>
                        </div>
                        <div class="stat-icon warning">
                            <span class="material-symbols-outlined">flag</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Tingkat Internasional</span>
                            <span class="stat-value">9</span>
                            <span class="stat-delta neutral">19% dari total</span>
                        </div>
                        <div class="stat-icon info">
                            <span class="material-symbols-outlined">public</span>
                        </div>
                    </div>
                    <div class="stat-card">
                        <div class="stat-info">
                            <span class="stat-label">Unit Inbis</span>
                            <span class="stat-value">12</span>
                            <span class="stat-delta neutral">25% dari total</span>
                        </div>
                        <div class="stat-icon success">
                            <span class="material-symbols-outlined">storefront</span>
                        </div>
                    </div>
                </div>

                <!-- FILTER BAR -->
                <div class="filter-card">
                    <div class="filter-grid">
                        <!-- Jenis -->
                        <div class="field">
                            <label class="field-label">Jenis Divisi</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-jenis" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Jenis</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua
                                        Jenis</button>
                                    <button type="button" class="dropdown-option" data-value="inbis">Inbis (Inkubator
                                        Bisnis)</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="kemahasiswaan">Kemahasiswaan</button>
                                </div>
                            </div>
                        </div>
                        <!-- Tab -->
                        <div class="field">
                            <label class="field-label">Kategori Tab</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-tab" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Tab</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua
                                        Tab</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="akademik">Akademik</button>
                                    <button type="button" class="dropdown-option" data-value="non_akademik">Non
                                        Akademik</button>
                                </div>
                            </div>
                        </div>
                        <!-- Tingkat -->
                        <div class="field">
                            <label class="field-label">Tingkat Capaian</label>
                            <div class="dropdown" data-dropdown>
                                <input type="hidden" id="filter-tingkat" value="semua" />
                                <button type="button" class="dropdown-trigger">
                                    <span class="dropdown-value">Semua Tingkat</span>
                                    <span class="material-symbols-outlined caret">expand_more</span>
                                </button>
                                <div class="dropdown-panel">
                                    <button type="button" class="dropdown-option is-selected" data-value="semua">Semua
                                        Tingkat</button>
                                    <button type="button" class="dropdown-option" data-value="lokal">Lokal
                                        (Kota/Wilayah)</button>
                                    <button type="button" class="dropdown-option"
                                        data-value="nasional">Nasional (RI)</button>
                                    <button type="button" class="dropdown-option" data-value="internasional">Internasional
                                        (Global)</button>
                                </div>
                            </div>
                        </div>
                        <!-- Tahun (locked) -->
                        <div class="field">
                            <label class="field-label">Tahun Akademik</label>
                            <div class="field-locked">
                                <div class="field-locked-inner">
                                    <span class="material-symbols-outlined">lock</span>
                                    <span class="field-locked-value">2026</span>
                                </div>
                            </div>
                        </div>
                        <!-- Search -->
                        <div class="field field-search-wide">
                            <label class="field-label" for="filter-search">Pencarian Cepat</label>
                            <div class="field-control">
                                <span class="material-symbols-outlined icon-search">search</span>
                                <input id="filter-search" type="text" placeholder="Cari NIM/nama mahasiswa..." />
                            </div>
                        </div>
                    </div>
                    <div class="filter-actions">
                        <button type="button" id="btn-reset-filter" class="btn-ghost">
                            <span class="material-symbols-outlined">restart_alt</span>
                            <span>Reset</span>
                        </button>
                        <button type="button" id="btn-apply-filter" class="btn-apply">
                            <span class="material-symbols-outlined">filter_alt</span>
                            <span>Terapkan Filter</span>
                        </button>
                    </div>
                </div>

                <!-- DATA TABLE CARD -->
                <div class="table-card">
                    <div class="table-card-header">
                        <div>
                            <h2 class="table-card-title">Daftar Rekap Prestasi Mahasiswa</h2>
                            <p class="table-card-subtitle">Data kegiatan terverifikasi sesuai format resmi SIM
                                Kemahasiswaan 2026</p>
                        </div>
                        <div class="table-card-tools">
                            <button type="button" class="tool-btn">
                                <span class="material-symbols-outlined">density_small</span>
                                <span>Kepadatan</span>
                            </button>
                            <button type="button" class="tool-btn">
                                <span class="material-symbols-outlined">view_column</span>
                                <span>Kolom</span>
                            </button>
                        </div>
                    </div>

                    <div class="table-scroll">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>NIM</th>
                                    <th>Mahasiswa</th>
                                    <th>Nama Kegiatan</th>
                                    <th class="center">Jenis</th>
                                    <th class="center">Tab</th>
                                    <th class="center">Tingkat</th>
                                    <th class="center">Tahun</th>
                                    <th class="center">Bukti Kegiatan</th>
                                    <th class="center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Row 1 -->
                                <tr>
                                    <td><span class="nim-code">222011005</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-primary">AR</div>
                                            <div class="student-name">
                                                <span class="name">Ahmad Rizal Fauzi</span>
                                                <span class="prodi">Teknik Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title"
                                            title="Juara 1 Kompetisi UI/UX Design Nasional TECHFEST 2026">Juara 1
                                            Kompetisi UI/UX Design Nasional TECHFEST 2026</span>
                                    </td>
                                    <td class="center"><span class="badge badge-primary">kemahasiswaan</span></td>
                                    <td class="center"><span class="badge badge-info">akademik</span></td>
                                    <td class="center">
                                        <span class="badge badge-warning">
                                            <span class="material-symbols-outlined">flag</span>nasional
                                        </span>
                                    </td>
                                    <td class="center"><span class="year-chip">2026</span></td>
                                    <td class="center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 2 -->
                                <tr>
                                    <td><span class="nim-code">232012014</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-info">SN</div>
                                            <div class="student-name">
                                                <span class="name">Siti Nurhaliza Putri</span>
                                                <span class="prodi">Sistem Informasi</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title"
                                            title="Pendanaan Startup Inbis: Smart Agrotech IoT">Pendanaan Startup
                                            Inbis: Smart Agrotech IoT</span>
                                    </td>
                                    <td class="center"><span class="badge badge-success">inbis</span></td>
                                    <td class="center"><span class="badge badge-info">akademik</span></td>
                                    <td class="center">
                                        <span class="badge badge-warning">
                                            <span class="material-symbols-outlined">flag</span>nasional
                                        </span>
                                    </td>
                                    <td class="center"><span class="year-chip">2026</span></td>
                                    <td class="center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 3 -->
                                <tr>
                                    <td><span class="nim-code">211009088</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-warning">KA</div>
                                            <div class="student-name">
                                                <span class="name">Kevin Ardiansyah</span>
                                                <span class="prodi">Desain Komunikasi Visual</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title"
                                            title="Juara 2 Debat Bahasa Inggris Tingkat Internasional NUDC">Juara 2
                                            Debat Bahasa Inggris Tingkat Internasional NUDC</span>
                                    </td>
                                    <td class="center"><span class="badge badge-primary">kemahasiswaan</span></td>
                                    <td class="center"><span class="badge badge-neutral">non_akademik</span></td>
                                    <td class="center">
                                        <span class="badge badge-danger">
                                            <span class="material-symbols-outlined">public</span>internasional
                                        </span>
                                    </td>
                                    <td class="center"><span class="year-chip">2026</span></td>
                                    <td class="center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 4 -->
                                <tr>
                                    <td><span class="nim-code">201007044</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-danger">RP</div>
                                            <div class="student-name">
                                                <span class="name">Rafi Pratama Wijaya</span>
                                                <span class="prodi">Teknik Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title"
                                            title="Publikasi Jurnal Nasional Terakreditasi SINTA 2 Bidang AI">Publikasi
                                            Jurnal Nasional Terakreditasi SINTA 2 Bidang AI</span>
                                    </td>
                                    <td class="center"><span class="badge badge-primary">kemahasiswaan</span></td>
                                    <td class="center"><span class="badge badge-info">akademik</span></td>
                                    <td class="center">
                                        <span class="badge badge-warning">
                                            <span class="material-symbols-outlined">flag</span>nasional
                                        </span>
                                    </td>
                                    <td class="center"><span class="year-chip">2026</span></td>
                                    <td class="center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 5 -->
                                <tr>
                                    <td><span class="nim-code">212010071</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-success">DM</div>
                                            <div class="student-name">
                                                <span class="name">Dinda Maharani</span>
                                                <span class="prodi">Manajemen Informatika</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title"
                                            title="Finalis Hackathon Nasional BUMN Innovation Week 2026">Finalis
                                            Hackathon Nasional BUMN Innovation Week 2026</span>
                                    </td>
                                    <td class="center"><span class="badge badge-primary">kemahasiswaan</span></td>
                                    <td class="center"><span class="badge badge-info">akademik</span></td>
                                    <td class="center">
                                        <span class="badge badge-warning">
                                            <span class="material-symbols-outlined">flag</span>nasional
                                        </span>
                                    </td>
                                    <td class="center"><span class="year-chip">2026</span></td>
                                    <td class="center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <!-- Row 6 -->
                                <tr>
                                    <td><span class="nim-code">202011099</span></td>
                                    <td>
                                        <div class="student-cell">
                                            <div class="avatar c-primary">CJ</div>
                                            <div class="student-name">
                                                <span class="name">Clara Jessica</span>
                                                <span class="prodi">Akuntansi &amp; Inbis</span>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="activity-title"
                                            title="Inkubasi Bisnis Mahasiswa Kemenpora 2026">Inkubasi Bisnis
                                            Mahasiswa Kemenpora 2026</span>
                                    </td>
                                    <td class="center"><span class="badge badge-success">inbis</span></td>
                                    <td class="center"><span class="badge badge-neutral">non_akademik</span></td>
                                    <td class="center">
                                        <span class="badge badge-neutral">
                                            <span class="material-symbols-outlined">location_on</span>lokal
                                        </span>
                                    </td>
                                    <td class="center"><span class="year-chip">2026</span></td>
                                    <td class="center">
                                        <a href="https://drive.google.com" target="_blank" rel="noopener noreferrer"
                                            class="evidence-link">
                                            <span class="material-symbols-outlined">cloud</span>
                                            <span>Lihat Bukti</span>
                                        </a>
                                    </td>
                                    <td class="center">
                                        <div class="row-actions">
                                            <button type="button" title="Lihat Detail" class="row-action-btn">
                                                <span class="material-symbols-outlined">visibility</span>
                                            </button>
                                            <button type="button" title="Opsi" class="row-action-btn is-secondary">
                                                <span class="material-symbols-outlined">more_vert</span>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- FOOTER: pagination -->
                    <div class="table-footer">
                        <div class="footer-summary">
                            Menampilkan <strong>1-6</strong> dari <strong>48</strong> data kegiatan mahasiswa tahun
                            <span class="highlight">2026</span>
                        </div>
                        <div class="pagination">
                            <button type="button" class="page-btn" disabled>
                                <span class="material-symbols-outlined">chevron_left</span>
                            </button>
                            <button type="button" class="page-btn is-active">1</button>
                            <button type="button" class="page-btn">2</button>
                            <button type="button" class="page-btn">3</button>
                            <span class="page-ellipsis">&hellip;</span>
                            <button type="button" class="page-btn">8</button>
                            <button type="button" class="page-btn">
                                <span class="material-symbols-outlined">chevron_right</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Custom dropdown behavior
        const dropdowns = document.querySelectorAll('[data-dropdown]');

        dropdowns.forEach((dropdown) => {
            const trigger = dropdown.querySelector('.dropdown-trigger');
            const valueEl = dropdown.querySelector('.dropdown-value');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            const options = dropdown.querySelectorAll('.dropdown-option');

            trigger.addEventListener('click', (e) => {
                e.stopPropagation();
                const wasOpen = dropdown.classList.contains('is-open');
                dropdowns.forEach((d) => d.classList.remove('is-open'));
                if (!wasOpen) dropdown.classList.add('is-open');
            });

            options.forEach((option) => {
                option.addEventListener('click', () => {
                    options.forEach((o) => o.classList.remove('is-selected'));
                    option.classList.add('is-selected');
                    valueEl.textContent = option.textContent.trim();
                    if (hiddenInput) hiddenInput.value = option.dataset.value;
                    dropdown.classList.remove('is-open');
                });
            });
        });

        document.addEventListener('click', () => {
            dropdowns.forEach((d) => d.classList.remove('is-open'));
        });

        function resetDropdown(dropdown) {
            if (!dropdown) return;
            const options = dropdown.querySelectorAll('.dropdown-option');
            const valueEl = dropdown.querySelector('.dropdown-value');
            const hiddenInput = dropdown.querySelector('input[type="hidden"]');
            options.forEach((o, i) => {
                o.classList.toggle('is-selected', i === 0);
                if (i === 0) {
                    valueEl.textContent = o.textContent.trim();
                    if (hiddenInput) hiddenInput.value = o.dataset.value;
                }
            });
        }

        document.getElementById('btn-reset-filter')?.addEventListener('click', () => {
            document.querySelectorAll('[data-dropdown]').forEach(resetDropdown);
            const search = document.getElementById('filter-search');
            if (search) search.value = '';
        });

        document.getElementById('btn-apply-filter')?.addEventListener('click', () => {
            const btn = document.getElementById('btn-apply-filter');
            if (btn) {
                const originalHTML = btn.innerHTML;
                btn.innerHTML = '<span class="material-symbols-outlined">progress_activity</span><span>Memuat...</span>';
                setTimeout(() => {
                    btn.innerHTML = originalHTML;
                }, 400);
            }
        });

        // Sidebar Drawer Toggle Logic
        const sidebar = document.querySelector('.app-sidebar');
        const sidebarToggleBtn = document.getElementById('sidebarToggleBtn');
        const sidebarCloseBtn = document.getElementById('sidebarCloseBtn');
        const sidebarOverlay = document.getElementById('sidebarOverlay');

        function openSidebar() {
            sidebar.classList.add('is-open');
            sidebarOverlay.classList.add('is-active');
            document.body.style.overflow = 'hidden'; // Mencegah scroll pada body saat sidebar terbuka
        }

        function closeSidebar() {
            sidebar.classList.remove('is-open');
            sidebarOverlay.classList.remove('is-active');
            document.body.style.overflow = '';
        }

        // Event Listeners
        sidebarToggleBtn?.addEventListener('click', openSidebar);
        sidebarCloseBtn?.addEventListener('click', closeSidebar);
        sidebarOverlay?.addEventListener('click', closeSidebar);

        // Otomatis menutup sidebar jika ukuran window diperbesar kembali ke mode desktop
        window.addEventListener('resize', () => {
            if (window.innerWidth >= 1024) {
                closeSidebar();
            }
        });

        // Dark Mode Toggle Logic
        const themeToggleBtn = document.getElementById('themeToggleBtn');
        const themeIcon = document.getElementById('themeIcon');

        // 1. Cek preferensi tema sebelumnya dari LocalStorage
        const savedTheme = localStorage.getItem('theme');
        if (savedTheme === 'dark') {
            document.body.classList.add('dark-mode');
            if (themeIcon) themeIcon.textContent = 'light_mode';
        }

        // 2. Event listener klik tombol
        themeToggleBtn?.addEventListener('click', () => {
            document.body.classList.toggle('dark-mode');

            const isDark = document.body.classList.contains('dark-mode');

            // Ubah ikon antara Bulan (dark_mode) dan Matahari (light_mode)
            if (themeIcon) {
                themeIcon.textContent = isDark ? 'light_mode' : 'dark_mode';
            }

            // Simpan pilihan user agar tidak hilang saat reload halaman
            localStorage.setItem('theme', isDark ? 'dark' : 'light');
        });
    </script>
</body>

</html>