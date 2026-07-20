#!/usr/bin/env python3
"""
Post-process pra-monitoring Excel Sheet2 via xlwings:
- Merge B:M for PA content, Note content, Kondisi checklist, Minor, Major rows
- Center + top alignment
- WrapText + RowHeight 32 if > 140 chars
Usage: python format_pra_sheet2.py <excel_path>
"""
import sys
import os
import xlwings as xw

SECTION_HEADERS_B = {
    "Note:", "Checklist tampilan gerai:",
    "Temuan dengan kategori Peringatan Awal (PA):",
    "Temuan dengan kategori MINOR:", "Temuan dengan kategori MAJOR:",
}


def format_pra_sheet2(wb):
    ws = wb.sheets[1]
    last_row = ws.used_range.last_cell.row
    print(f"Sheet2 has {last_row} rows")

    header_rows = set()

    for rn in range(1, last_row + 1):
        val_a = ws.range(f"A{rn}").value or ""
        val_b = ws.range(f"B{rn}").value or ""
        sa = str(val_a).strip()
        sb = str(val_b).strip()

        if sa.startswith("A.") and "Peringatan Awal" in sb:
            header_rows.add(rn)
            print(f"PA header at row {rn}")
        elif sb in SECTION_HEADERS_B:
            header_rows.add(rn)
            print(f"Section header at row {rn}: '{sb}'")
        elif sa.startswith("B.") and "MINOR" in sb:
            header_rows.add(rn)
            print(f"MINOR header at row {rn}")
        elif sa.startswith("C.") and "MAJOR" in sb:
            header_rows.add(rn)
            print(f"MAJOR header at row {rn}")

    if not header_rows:
        print("No section headers found, skipping")
        return 0

    merge_rows = []
    header_list = sorted(header_rows)

    for i in range(len(header_list)):
        start = header_list[i] + 1
        end = header_list[i + 1] if i + 1 < len(header_list) else last_row + 1
        for rn in range(start, end):
            val = ws.range(f"B{rn}").value
            if val is None:
                continue
            s = str(val).strip()
            if s == "":
                continue
            merge_rows.append(rn)

    print(f"  Total content rows: {len(merge_rows)}")

    for rn in merge_rows:
        ws.range(f"B{rn}:M{rn}").api.Merge()
        cell = ws.range(f"B{rn}")
        cell.api.HorizontalAlignment = -4131  # center
        cell.api.VerticalAlignment = -4160    # top
        cell.api.WrapText = True
        val = ws.range(f"B{rn}").value or ""
        if len(str(val).strip()) > 118:
            ws.range(f"{rn}:{rn}").api.RowHeight = 32

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
        total = format_pra_sheet2(wb)
        wb.save()
        wb.close()
        print(f"Done: formatted {total} rows")
    except Exception as e:
        print(f"Error: {e}")
    finally:
        try:
            app.quit()
        except Exception:
            pass


if __name__ == "__main__":
    main()
