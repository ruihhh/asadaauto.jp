#!/usr/bin/env bash
# asadaauto.jp: ホスト（ローカル）での npm/composer/php/node 等の直接実行をブロックする。
# docker compose 経由（docker compose exec/run ... または docker-compose ...）なら許可。
# PreToolUse(Bash) フックとして使用。stdin に tool_input.command が入った JSON を受け取る。

input=$(cat)
cmd=$(printf '%s' "$input" | jq -r '.tool_input.command // ""')

# docker compose / docker-compose を経由しているコマンドは許可
if printf '%s' "$cmd" | grep -qE 'docker[ -]compose'; then
  exit 0
fi

# コマンド語としての禁止ツール（行頭・パイプ・; & の直後・空白区切り）
forbidden='npm|npx|pnpm|yarn|composer|php|node|artisan'
if printf '%s' "$cmd" | grep -qE "(^|[;&|(]|[[:space:]])($forbidden)([[:space:]]|$)"; then
  reason='asadaauto.jp ではコマンドを必ず docker compose 経由で実行してください（例: docker compose exec app '"$(printf '%s' "$cmd" | grep -oE "($forbidden)" | head -1)"' ...）。ホストでの直接実行はブロックされています。'
  jq -n --arg r "$reason" '{hookSpecificOutput:{hookEventName:"PreToolUse",permissionDecision:"deny",permissionDecisionReason:$r}}'
  exit 0
fi

exit 0
