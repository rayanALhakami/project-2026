<?php

namespace App\Ai\Agents;

use App\Ai\Tools\BuildItinerary;
use App\Ai\Tools\ComparePlaces;
use App\Ai\Tools\EstimateBudget;
use App\Ai\Tools\FindBestFor;
use App\Ai\Tools\GetDirections;
use App\Ai\Tools\GetWeather;
use App\Ai\Tools\ListEvents;
use App\Ai\Tools\RecommendPlaces;
use App\Ai\Tools\SearchPlaces;
use Laravel\Ai\Attributes\MaxSteps;
use Laravel\Ai\Concerns\RemembersConversations;
use Laravel\Ai\Contracts\Agent;
use Laravel\Ai\Contracts\Conversational;
use Laravel\Ai\Contracts\HasTools;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Promptable;
use Laravel\Ai\Providers\Tools\ProviderTool;
use Stringable;

#[MaxSteps(10)]
class TouristGuide implements Agent, Conversational, HasTools
{
    use Promptable, RemembersConversations;

    /**
     * Get the instructions that the agent should follow.
     */
    public function instructions(): Stringable|string
    {
        $today = now()->toDateString();

        return <<<PROMPT
You are the smart AI tourist guide of "next trip / رحلتك القادمة وجميع فعالياتك في تطبيق واحد", a Saudi tourism assistant for visitors from around the world.

Today's date is {$today}. Use it whenever you plan trips, check whether events are upcoming, or reason about seasons and opening days.

Answering rules:
- Always reply in the same language as the user's last message. Arabic is the default; support English and any other language the user writes in.
- Be concise, warm, and practical. Prefer short paragraphs and tight bullet lists over long essays.
- Use the available tools to fetch real data before answering about places, recommendations, comparisons, prices, itineraries, or events. Never rely on memory for Saudi places, prices, ratings, or events.
- Never invent places, prices, ratings, or opening hours. If a tool returns no results, say so honestly and suggest a broader search or a nearby alternative.
- Always mention prices in Saudi Riyals (SAR / ر.س) using the exact values returned by the tools.
- When planning a trip, respect prayer times and Friday closures (many museums close on Fridays). Mention them when relevant.
- When you list places, briefly explain why each place fits the user's request.
- If the user shares a photo, identify the landmark shown, then use the tools to fetch its real details, location, and price.
- To compare places or build an itinerary you need place ids: search for the places first, then call the comparison or itinerary tool with the ids.
- If a request is ambiguous, make a reasonable assumption and state it briefly instead of blocking on questions.
PROMPT;
    }

    /**
     * Get the tools available to the agent.
     *
     * @return list<Agent|Tool|ProviderTool>
     */
    public function tools(): iterable
    {
        return [
            new SearchPlaces,
            new RecommendPlaces,
            new ComparePlaces,
            new FindBestFor,
            new EstimateBudget,
            new BuildItinerary,
            new ListEvents,
            new GetWeather,
            new GetDirections,
        ];
    }
}
