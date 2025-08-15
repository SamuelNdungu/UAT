<!DOCTYPE html>
<html>
<head>
    <title>Customers PDF</title>
    <style>
        /* General body styling */
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        /* Heading alignment */
        h1 {
            text-align: center;
            margin-bottom: 20px;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 7px; /* Reduced font size */
        }

        th, td {
            padding: 5px;
            text-align: left;
            border: 1px solid #dddddd;
        }

        th {
            background-color: #f2f2f2;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        /* CSS for landscape orientation during print */
        @page {
            size: A3 landscape;
            margin: 1mm; /* Adjust margins if needed */
        }

        @media print {
            body {
                -webkit-print-color-adjust: exact; /* Ensure colors are printed correctly */
            }
            table {
                width: 100%; /* Force the table to fit the page */
                font-size: 10px; /* Further reduce font size for print */
            }

            th, td {
                padding: 3px; /* Reduce padding to save space */
            }

            h1 {
                font-size: 12px; /* Reduce the title font size */
            }
        }
    </style>
</head>
<body>
    <h1>Customers List</h1>
    <table>
        <thead>
            <tr>
                <th>Code</th>
                <th>Customer Name</th>
                <th>Email</th>
                <th>Phone</th>
                <th>Customer Type</th>
                <th>Title</th>
                <th>DOB</th>
                <th>Occupation</th>
                <th>Corporate Name</th>
                <th>Business No</th>
                <th>Contact Person</th>
                <th>Designation</th>
                <th>Industry Class</th>
                <th>Industry Segment</th>
                <th>Address</th>
                <th>City</th>
                <th>County</th>
                <th>Postal Code</th>
                <th>Country</th>
                <th>ID Number</th>
                <th>KRA PIN</th> 
                <th>Status</th>
                <th>Created At</th>
                <th>Updated At</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($customers as $customer)
                <tr>
                    <td>{{ $customer->customer_code }}</td>
                    <td>{{ $customer->customer_name }}</td>
                    <td>{{ $customer->email }}</td>
                    <td>{{ $customer->phone }}</td>
                    <td>{{ $customer->customer_type }}</td>
                    <td>{{ $customer->title }}</td>
                    <td>{{ $customer->dob }}</td>
                    <td>{{ $customer->occupation }}</td>
                    <td>{{ $customer->corporate_name }}</td>
                    <td>{{ $customer->business_no }}</td>
                    <td>{{ $customer->contact_person }}</td>
                    <td>{{ $customer->designation }}</td>
                    <td>{{ $customer->industry_class }}</td>
                    <td>{{ $customer->industry_segment }}</td>
                    <td>{{ $customer->address }}</td>
                    <td>{{ $customer->city }}</td>
                    <td>{{ $customer->county }}</td>
                    <td>{{ $customer->postal_code }}</td>
                    <td>{{ $customer->country }}</td>
                    <td>{{ $customer->id_number }}</td>
                    <td>{{ $customer->kra_pin }}</td> 
                    <td>{{ $customer->status ? 'Active' : 'Inactive' }}</td>
                    <td>{{ $customer->created_at }}</td>
                    <td>{{ $customer->updated_at }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</body>
</html>
