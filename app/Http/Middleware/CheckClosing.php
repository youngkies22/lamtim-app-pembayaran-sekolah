<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Closing;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class CheckClosing
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $dateField = 'date'): Response
    {
        // Skip read methods
        if ($request->isMethodSafe()) {
            return $next($request);
        }

        $dateToCheck = null;

        // 1. Check if date is in request input (Priority for changes)
        // This handles POST (create) and PUT (update with new date)
        if ($request->has($dateField)) {
            $dateToCheck = $request->input($dateField);
        } 
        // 2. If not in input, try to find in route parameters (e.g., for DELETE or PUT without date change)
        else {
            foreach ($request->route()->parameters() as $parameter) {
                if ($parameter instanceof Model) {
                    // Check if model has the attribute
                    // We access it as an attribute to handle accessors or raw values
                    if (isset($parameter->$dateField)) {
                        $dateToCheck = $parameter->$dateField;
                        break;
                    } 
                    // Fallback for common date fields if $dateField is generic
                    elseif ($dateField === 'date') {
                        if (isset($parameter->tanggal)) $dateToCheck = $parameter->tanggal;
                        elseif (isset($parameter->date)) $dateToCheck = $parameter->date;
                        elseif (isset($parameter->created_at)) $dateToCheck = $parameter->created_at;
                    }
                }
            }
        }

        if ($dateToCheck) {
            try {
                if (Closing::isDateClosed($dateToCheck)) {
                    $formattedDate = Carbon::parse($dateToCheck)->format('d M Y');
                    return response()->json([
                        'message' => "Action denied. The period for {$formattedDate} is closed.",
                        'error_code' => 'PERIOD_CLOSED'
                    ], 403);
                }
            } catch (\Exception $e) {
                // Ignore parsing errors, proceed
            }
        }

        return $next($request);
    }
}
