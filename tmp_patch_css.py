from pathlib import Path

path = Path('c:/Unizar/2ºcuatri/PS/educattio/css/detalles_curso.css')
text = path.read_text(encoding='utf-8')
old = '''#calendar {
    max-width: 100%;
    min-height: 380px;
}

.btn-add-class {
    border: 1px solid #d1d5db;
    background: #fff;
    color: #111827;
    border-radius: 999px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-add-class:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.section-divider {
'''
new = '''#calendar {
    max-width: 100%;
    min-height: 380px;
}

.btn-add-class {
    border: 1px solid #d1d5db;
    background: #fff;
    color: #111827;
    border-radius: 999px;
    padding: 0.75rem 1rem;
    font-size: 0.9rem;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    transition: all 0.2s ease;
}

.btn-add-class:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
}

.calendar-card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: 0.75rem;
    flex-wrap: wrap;
}

.small-label {
    display: block;
    font-size: 0.75rem;
    color: #6b7280;
    text-transform: uppercase;
    letter-spacing: 0.08em;
    margin-bottom: 0.35rem;
}

.today-card {
    display: grid;
    gap: 1rem;
    text-align: left;
}

.today-label {
    font-size: 0.85rem;
    text-transform: uppercase;
    color: #6b7280;
    letter-spacing: 0.12em;
}

.today-day-name {
    font-size: 1rem;
    color: #4b5563;
}

.today-number {
    font-size: 3rem;
    font-weight: 700;
    color: #111827;
}

.today-month-year,
.today-clock {
    color: #6b7280;
    font-size: 0.95rem;
}

.section-divider {
'''
if old in text:
    text = text.replace(old, new)
    path.write_text(text, encoding='utf-8')
    print('Patched detalles_curso.css')
else:
    print('Pattern not found, no patch applied')
