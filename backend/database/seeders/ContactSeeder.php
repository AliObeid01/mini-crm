<?php

namespace Database\Seeders;

use App\Models\Contact;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ContactSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $csvPath = database_path('seeders/data/contacts.csv');

        if (!File::exists($csvPath)) {
            $this->command->warn("CSV file not found at: {$csvPath}");
            return;
        }

        $this->importFromCsv($csvPath);
    }

    /**
     * Import contacts from CSV file.
     */
    private function importFromCsv(string $csvPath): void
    {
        $handle = fopen($csvPath, 'r');
        
        if ($handle === false) {
            $this->command->error("Could not open CSV file: {$csvPath}");
            return;
        }

        // Get headers
        $headers = fgetcsv($handle);
        $headers = array_map('trim', $headers);

        // Map CSV headers to database columns
        $headerMap = [
            'First name' => 'first_name',
            'Last Name' => 'last_name',
            'Phone Number' => 'phone_number',
            'Birthdate' => 'birthdate',
            'City' => 'city',
        ];

        $imported = 0;
        $updated = 0;

        DB::transaction(function () use ($handle, $headers, $headerMap, &$imported, &$updated) {
            while (($row = fgetcsv($handle)) !== false) {
                $data = array_combine($headers, $row);
                
                $contactData = [];
                foreach ($headerMap as $csvHeader => $dbColumn) {
                    if (isset($data[$csvHeader])) {
                        $contactData[$dbColumn] = trim($data[$csvHeader]);
                    }
                }

                // Handle birthdate format
                if (!empty($contactData['birthdate'])) {
                    try {
                        $contactData['birthdate'] = date('Y-m-d', strtotime($contactData['birthdate']));
                    } catch (\Exception $e) {
                        $contactData['birthdate'] = null;
                    }
                }

                // Check if contact exists (assuming phone_number is unique identifier)
                $existingContact = Contact::where('phone_number', $contactData['phone_number'])->first();
                
                if ($existingContact) {
                    $existingContact->update($contactData);
                    $updated++;
                } else {
                    Contact::create($contactData);
                    $imported++;
                }
            }
        });

        fclose($handle);

        $this->command->info("Imported {$imported} new contacts and updated {$updated} existing contacts from CSV!");
    }

}
