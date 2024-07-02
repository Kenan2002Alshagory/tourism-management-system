<?php

namespace App\Http\Controllers;

use App\Models\Reservation;
use App\Models\Review;
use App\Models\Trip;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewsController extends Controller
{
    public function createReview(Request $request, $tripId)
    {
        $user = Auth::user();
        $trip = Trip::find($tripId);
        if (!$trip) {
            return response()->json(['message' => 'Trip not found'], 404);
        }
        $reservation = Reservation::where('trip_id', $tripId)->where('user_id', $user->id)->first();

        if (!$reservation) {
            return response()->json(['message' => 'You cannot review a trip you have not reserved'], 403);
        }
        if ($trip->status_time !== 'Ending') {
            return response()->json(['message' => 'You can only review a trip that has ended'], 403);
        }

        $validated = $request->validate([
            'rating' => 'required|numeric|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::create([
            'trip_id' => $tripId,
            'user_id' => $user->id,
            'rating' => $validated['rating'],
            'comment' => $validated['comment'],
        ]);

        return response()->json(['message' => 'Review added successfully', 'review' => $review]);
    }

    public function getReviews($tripId)
    {
        $reviews = Review::where('trip_id', $tripId)
        ->join('users as u','u.id','reviews.user_id')
        ->get();

        return response()->json(['reviews' => $reviews]);
    }

    public function deleteReview($reviewId)
    {
        $user = Auth::user();
        $review = Review::find($reviewId);

        if (!$review) {
            return response()->json(['message' => 'Review not found'], 404);
        }

        if ($user->id !== $review->user_id && $user->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $review->delete();

        return response()->json(['message' => 'Review deleted successfully']);
    }
}
