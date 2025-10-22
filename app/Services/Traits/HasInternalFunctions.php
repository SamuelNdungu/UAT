<?php


namespace App\Services\Traits;

trait HasInternalFunctions
{
    /**
     * Defines all available tools (functions) for the language model.
     * * @return array The array of tool declarations.
     */
    protected function getToolDeclarations(): array
    {
        return [
            [
                'functionDeclarations' => [
                    // Function 1: Get policies by status
                    [
                        'name' => 'get_insurance_policies',
                        'description' => 'Retrieves a list of insurance policies filtered by their current status.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'status' => [
                                    'type' => 'STRING',
                                    'description' => 'The policy status to filter by. Must be either "CLAIMED" (has claims filed) or "ACTIVE" (has no claims filed).'
                                ]
                            ],
                            'required' => ['status']
                        ]
                    ],
                    // Function 2: Get policies by outstanding balance
                    [
                        'name' => 'get_policies_by_balance',
                        'description' => 'Retrieves a list of policies that currently have an outstanding premium balance or negative financial value (e.g., negative Sum Insured or Premium fields).',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'balance_status' => [
                                    'type' => 'STRING',
                                    'description' => 'The balance status. Must be "OUTSTANDING" to find policies with negative financial figures, such as Premium or Sum Insured, as shown in the system\'s Financial Details section.'
                                ]
                            ],
                            'required' => ['balance_status']
                        ]
                    ]
                ]
            ]
        ];
    }
    
    /**
     * Local implementation of the get_insurance_policies function.
     * * @param string $status The policy status.
     * @return string JSON representation of the function result.
     */
    protected function get_insurance_policies(string $status): string
    {
        $status = strtoupper($status);
        if ($status === 'CLAIMED') {
            $policies = [
                ['customer' => 'Acme Corp', 'file_number' => 'FN-00004', 'total_claims' => 1],
                ['customer' => 'Acme Corp', 'file_number' => 'FN-00003', 'total_claims' => 1],
                ['customer' => 'Simon Ndungu', 'file_number' => 'FN-00014', 'total_claims' => 1],
            ];
            $count = count($policies);
            $summary = "There are {$count} policies with claims filed against them.";
        } elseif ($status === 'ACTIVE') {
            $policies = [
                ['customer' => 'Jane Doe', 'file_number' => 'FN-00050', 'total_claims' => 0],
                ['customer' => 'Mali Traders', 'file_number' => 'FN-00051', 'total_claims' => 0],
            ];
            $count = count($policies);
            $summary = "There are {$count} active policies with no claims filed.";
        } else {
            $policies = [];
            $summary = "I can only get policies that are 'ACTIVE' (no claims) or 'CLAIMED' (has claims). I cannot filter by policies that have an outstanding balance.";
        }

        return json_encode(['summary' => $summary, 'policies' => $policies]);
    }

    /**
     * Local implementation of the get_policies_by_balance function.
     * This addresses the user's need to find outstanding balances by looking for negative financial fields.
     * * @param string $balanceStatus Must be "OUTSTANDING".
     * @return string JSON representation of the function result.
     */
    protected function get_policies_by_balance(string $balanceStatus): string
    {
        $balanceStatus = strtoupper($balanceStatus);

        if ($balanceStatus === 'OUTSTANDING') {
            // Mock data reflecting negative values seen in image_ed7549.png
            $policies = [
                [
                    'file_number' => 'FN-00052', 
                    'customer' => 'Global Marine Logistics', 
                    'issue' => 'Negative Premium (-1,680.21) or Sum Insured (-56,007.00)',
                    'status' => 'Requires Correction'
                ],
                [
                    'file_number' => 'FN-00031', 
                    'customer' => 'Zimmerman Holdings', 
                    'issue' => 'Negative Net Premium (-5,100.00)',
                    'status' => 'Pending Review'
                ],
            ];
            $count = count($policies);
            $summary = "There are {$count} policies identified with outstanding issues (negative financial figures).";
        } else {
            $policies = [];
            $summary = "I can only filter policies by 'OUTSTANDING' balance status.";
        }

        return json_encode(['summary' => $summary, 'policies' => $policies]);
    }
}
