/**
 * Shape of a rich assistant reply. The mock fills it locally today; the
 * real AI tools will return the same structure so the UI never changes.
 */
export interface AssistantComparison {
    columns: string[];
    rows: { label: string; values: string[] }[];
}

export interface AssistantReply {
    text: string;
    places?: number[];
    mapPlaces?: number[];
    comparison?: AssistantComparison;
}
