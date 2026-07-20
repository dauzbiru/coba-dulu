#!/usr/bin/env python3
"""
Post-process monitoring Excel via xlwings:
- Sheet 2: Merge A:M for Penjelasan Formulir 2 rows
- Sheet 3: Merge B:O for PA content, Note, Kondisi, Penjelasan Formulir 3 rows
- Apply wrap text + row height 32 for rows > 140 chars
Usage: python format_pa_rows.py <excel_path>
"""
import sys
import os
import xlwings as xw

def format_sheet2(wb):
    ws = wb.sheets[1]
    last_row = ws.used_range.last_cell.row
    print(f"Sheet2 has {last_row} rows")

    penjelasan_header_row = None
    for rn in range(1, last_row + 1):
        val = ws.range(f"A{rn}").value
        if val and "PENJELASAN" in str(val).strip():
            penjelasan_header_row = rn
            print(f"Sheet2: Penjelasan header at row {rn}")
            break

    if penjelasan_header_row is None:
        print("Sheet2: No 'PENJELASAN' found")
        return 0

    # Detect case: check if penjelasan content is in A or B
    # Non Temuan = content in A; Ada low items = content in B
    content_col = "A"
    merge_col_start = "A"
    for rn in range(penjelasan_header_row + 1, penjelasan_header_row + 5):
        val_b = ws.range(f"B{rn}").value
        if val_b and str(val_b).strip():
            content_col = "B"
            merge_col_start = "B"
            print(f"Sheet2: Content in column B (low items case)")
            break

    merge_rows = []
    for rn in range(penjelasan_header_row + 1, last_row + 1):
        val = ws.range(f"{content_col}{rn}").value
        if val is None:
            break
        s = str(val).strip()
        if s == "":
            break
        merge_rows.append(rn)
        print(f"  Sheet2 penjelasan row {rn}: {s[:50]}")

    for rn in merge_rows:
        ws.range(f"{merge_col_start}{rn}:M{rn}").api.Merge()
        cell = ws.range(f"{merge_col_start}{rn}")
        cell.api.HorizontalAlignment = -4131
        cell.api.VerticalAlignment = -4160
        cell.api.WrapText = True
        val = ws.range(f"{merge_col_start}{rn}").value or ""
        if len(str(val).strip()) > 140:
            ws.range(f"{merge_col_start}{rn}").api.RowHeight = 32

    return len(merge_rows)

def format_sheet3(wb):
    ws = wb.sheets[2]
    last_row = ws.used_range.last_cell.row
    print(f"Sheet3 has {last_row} rows")

    pa_header_row = None
    note_header_row = None

    for row in range(1, last_row + 1):
        val = ws.range(f"B{row}").value
        if val:
            s = str(val).strip()
            if "Peringatan Awal" in s and pa_header_row is None:
                pa_header_row = row
                print(f"PA header found at row {row}: {s[:60]}")
            elif s == "NOTE:" and pa_header_row is not None and note_header_row is None:
                note_header_row = row
                print(f"NOTE found at row {row}")
                break

    if pa_header_row is None:
        print("Sheet3: No 'Peringatan Awal' found")
        return 0

    if note_header_row is None:
        note_header_row = last_row + 1

    merge_rows = []

    # PA content: skip pengawas/AJ/TDS/MO, find PA lines after empty separator
    found_empty = False
    for rn in range(pa_header_row + 1, note_header_row):
        val = ws.range(f"B{rn}").value
        s = str(val).strip() if val else ""

        if s == "":
            if not found_empty:
                found_empty = True
            continue

        if found_empty:
            merge_rows.append(rn)
            print(f"  PA row {rn}: {s[:50]}")

    # Note + Kondisi rows (after NOTE header, skip empty & "Checklist" header)
    for rn in range(note_header_row + 1, last_row + 1):
        val = ws.range(f"B{rn}").value
        if val is None:
            continue
        s = str(val).strip()
        if s == "" or s == "Checklist tampilan gerai:":
            continue
        merge_rows.append(rn)
        print(f"  Note/Kondisi row {rn}: {s[:50]}")

    # Penjelasan Formulir 3 rows (after "PENJELASAN:" header, numbered items)
    penjelasan_header_row = None
    for rn in range(1, last_row + 1):
        val = ws.range(f"A{rn}").value
        if val and "PENJELASAN" in str(val).strip():
            penjelasan_header_row = rn
            print(f"Penjelasan header found at row {rn}")
            break

    if penjelasan_header_row is not None:
        for rn in range(penjelasan_header_row + 1, last_row + 1):
            val = ws.range(f"B{rn}").value
            if val is None:
                break
            s = str(val).strip()
            if s == "":
                break
            merge_rows.append(rn)
            print(f"  Penjelasan row {rn}: {s[:50]}")

    for rn in merge_rows:
        ws.range(f"B{rn}:O{rn}").api.Merge()
        cell = ws.range(f"B{rn}")
        cell.api.HorizontalAlignment = -4131
        cell.api.VerticalAlignment = -4160
        cell.api.WrapText = True
        val = ws.range(f"B{rn}").value or ""
        if len(str(val).strip()) > 140:
            ws.range(f"B{rn}").api.RowHeight = 32

    return len(merge_rows)

def main():
    if len(sys.argv) < 2:
        print("No args")
        sys.exit(0)

    excel_path = os.path.abspath(sys.argv[1])
    if not os.path.exists(excel_path):
        print(f"File not found: {excel_path}")
        sys.exit(0)

    app = xw.App(visible=False)
    try:
        wb = app.books.open(excel_path)
        total = 0
        total += format_sheet2(wb)
        total += format_sheet3(wb)
        wb.save()
        wb.close()
        print(f"Done: merged {total} rows total")
    except Exception as e:
        print(f"Error: {e}")
    finally:
        try:
            app.quit()
        except Exception:
            pass

if __name__ == "__main__":
    main()
