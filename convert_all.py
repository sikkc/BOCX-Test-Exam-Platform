import csv
import os
import openpyxl

INPUT_DIR = r'C:\xampp\htdocs\BOCX-Test-Exam-Platform\excel_files'
OUTPUT_DIR = r'C:\xampp\htdocs\BOCX-Test-Exam-Platform\csv_files'

os.makedirs(INPUT_DIR, exist_ok=True)
os.makedirs(OUTPUT_DIR, exist_ok=True)

excel_files = [f for f in os.listdir(INPUT_DIR) if f.endswith('.xlsx') and not f.startswith('~$')]

if not excel_files:
    print(f"No .xlsx files found in {INPUT_DIR}!")
    exit()

print(f"Processing {len(excel_files)} Excel file(s)...\n")

for filename in excel_files:
    excel_path = os.path.join(INPUT_DIR, filename)
    csv_filename = os.path.splitext(filename)[0] + '.csv'
    csv_path = os.path.join(OUTPUT_DIR, csv_filename)
    
    wb = openpyxl.load_workbook(excel_path, data_only=True)
    sheet = wb.active
    
    questions_data = []
    
    # Iterate through rows starting from Row 2 (skipping headers)
    for row in sheet.iter_rows(min_row=2, values_only=True):
        if not row or not row[0]: # Skip empty rows
            continue
            
        q_text  = str(row[0]).strip() if row[0] is not None else ""
        opt_a   = str(row[1]).strip() if len(row) > 1 and row[1] is not None else ""
        opt_b   = str(row[2]).strip() if len(row) > 2 and row[2] is not None else ""
        opt_c   = str(row[3]).strip() if len(row) > 3 and row[3] is not None else ""
        opt_d   = str(row[4]).strip() if len(row) > 4 and row[4] is not None else ""
        correct = str(row[5]).strip().upper() if len(row) > 5 and row[5] is not None else "A"
        
        if q_text and opt_a and opt_b and opt_c and opt_d:
            questions_data.append([q_text, opt_a, opt_b, opt_c, opt_d, correct])

    # Write output CSV preserving original filename
    with open(csv_path, 'w', newline='', encoding='utf-8') as f:
        writer = csv.writer(f)
        writer.writerow(['question_text', 'option_a', 'option_b', 'option_c', 'option_d', 'correct_option'])
        writer.writerows(questions_data)

    print(f"✔ Successfully converted: '{filename}' -> '{csv_filename}' ({len(questions_data)} questions)")

print("\nBatch conversion complete!")