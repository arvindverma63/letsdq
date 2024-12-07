<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;


class SessionController extends Controller
{
    /**
     * Store or update session data and return all session data as a response.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function addData(Request $request)
    {
        // Validate the request based on the type of data provided
        $validator = Validator::make($request->all(), [
            'itemName' => 'required_with:itemId,price|string|max:255',
            'itemId' => 'required_with:itemName,price|integer',
            'price' => 'required_with:itemName,itemId|numeric|min:0',
            'id' => 'nullable|integer',
            'name' => 'nullable|string|max:255',
            'tableNumber' => 'required|integer', // Ensure table number is required
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $tableNumber = $request->input('tableNumber');

        // Add or update customer information in the session
        if ($request->has('id')) {
            session()->put("tables.$tableNumber.id", $request->input('id'));
        }
        if ($request->has('name')) {
            session()->put("tables.$tableNumber.name", $request->input('name'));
        }

        // Handle items with quantity and price tracking for a specific table
        $tables = session()->get('tables', []); // Get all tables data or initialize as empty
        $items = $tables[$tableNumber]['items'] ?? []; // Get items for the specific table

        $itemExists = false;

        if ($request->has(['itemId', 'itemName', 'price'])) {
            foreach ($items as &$item) {
                if ((int) $item['itemId'] === (int) $request->input('itemId')) {
                    // If the item exists, increment its quantity
                    $item['quantity'] += 1;
                    $itemExists = true;
                    break;
                }
            }

            // Add a new item if it doesn't exist
            if (!$itemExists) {
                $items[] = [
                    'itemId' => $request->input('itemId'),
                    'itemName' => $request->input('itemName'),
                    'price' => $request->input('price'),
                    'quantity' => 1,
                ];
            }

            // Update the session with the modified items array for the specific table
            session()->put("tables.$tableNumber.items", $items);
        }

        // Retrieve all session data for the specific table
        $sessionData = session()->get("tables.$tableNumber");

        $message = $itemExists
            ? 'Item quantity updated successfully!'
            : 'New item added to the table.';

        return response()->json([
            'data' => $sessionData,
            'message' => $message,
            'success' => true,
        ]);
    }


    /**
     * Clear all session data.
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function clearSession($tableNumber)
    {
        // Remove data only for the specific table number
        session()->forget("tables.$tableNumber");

        return redirect()->back()->with('message', 'Session data for table cleared successfully!');
    }


    public function removeItem(Request $request)
    {
        // Validate input
        $request->validate([
            'itemId' => 'required|integer',
            'tableNumber' => 'required|integer',
        ]);

        $tableNumber = $request->input('tableNumber');

        // Retrieve the items for the specific table
        $items = session()->get("tables.$tableNumber.items", []);

        // Filter out the item to be removed
        $items = array_filter($items, function ($item) use ($request) {
            return $item['itemId'] != $request->input('itemId');
        });

        // Re-index the array to maintain consistent keys after filtering
        $items = array_values($items);

        // Update the session with the modified items array for the specific table
        session()->put("tables.$tableNumber.items", $items);

        return response()->json([
            'message' => 'Item removed successfully!',
            'items' => $items,  // Optional: return the updated cart items
            'success' => true,
        ]);
    }

    public function getCart($tableNumber)
    {
        // Retrieve the cart items for the specific table
        $items = session()->get("tables.$tableNumber.items", []);

        // Construct the response data
        $responseData = [
            'data' => [
                'items' => $items,
            ],
            'message' => 'Cart fetched successfully!',
            'success' => true,
        ];

        return response()->json($responseData);
    }


    public function getData($tableNumber)
    {
        // Retrieve all tables data from the session
        $tables = session()->get('tables', []);

        // Check if data exists for the requested table number
        if (isset($tables[$tableNumber])) {
            $sessionData = $tables[$tableNumber];

            return response()->json([
                'data' => $sessionData,
                'message' => 'Table data retrieved successfully!',
                'success' => true,
            ]);
        }

        // If no data found for the specified table
        return response()->json([
            'data' => null,
            'message' => 'No data found for this table.',
            'success' => false,
        ], 404);
    }
}
