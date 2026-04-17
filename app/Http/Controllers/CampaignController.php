<?php

namespace App\Http\Controllers;

use App\Campaign;
use Carbon\Carbon;
use Illuminate\Http\Request;

class CampaignController extends Controller
{
    public function index()
    {
        $campaigns = Campaign::query()
            ->withCount('offers')
            ->orderBy('name')
            ->get();

        return view('advertiser.index', compact('campaigns'));
    }

    public function create()
    {
        return view('advertiser.form', [
            'campaign' => new Campaign(),
            'mode' => 'create',
            'pageTitle' => 'Create Advertiser',
            'formAction' => '/advertisers/create',
        ]);
    }

    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required|string|max:155',
        ]);

        $campaign = new Campaign();
        $campaign->name = $request->input('name');
        $campaign->timestamp = Carbon::now('UTC')->timestamp;
        $campaign->save();

        return redirect('/advertisers')->with('message', 'Advertiser created successfully.');
    }

    public function edit($id)
    {
        $campaign = Campaign::query()
            ->with(['offers' => function ($query) {
                $query->orderBy('offer_name');
            }])
            ->findOrFail($id);

        return view('advertiser.form', [
            'campaign' => $campaign,
            'mode' => 'edit',
            'pageTitle' => 'Edit Advertiser',
            'formAction' => "/advertisers/{$campaign->id}/edit",
        ]);
    }

    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'name' => 'required|string|max:155',
        ]);

        $campaign = Campaign::query()->findOrFail($id);
        $campaign->name = $request->input('name');
        $campaign->save();

        return redirect("/advertisers/{$campaign->id}/edit")->with('message', 'Advertiser updated successfully.');
    }
}
