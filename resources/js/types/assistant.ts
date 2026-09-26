export type ChatRole = 'user' | 'assistant';

export type ToolCallStatus = 'running' | 'success' | 'error';

export type ToolCallView = {
    id: string;
    name: string;
    arguments: Record<string, unknown>;
    status: ToolCallStatus;
    summary?: string;
};

export type ChatMessage = {
    id: string;
    role: ChatRole;
    content: string;
    toolCalls: ToolCallView[];
    error?: string;
};

export type AssistantHistoryItem = {
    role: ChatRole;
    content: string;
};

export type AssistantFrame =
    | { type: 'text'; delta: string }
    | {
          type: 'tool_call';
          id: string;
          name: string;
          arguments: Record<string, unknown>;
      }
    | {
          type: 'tool_result';
          id: string;
          name: string;
          summary: string;
          ok: boolean;
      }
    | { type: 'error'; message: string }
    | { type: 'done' };
