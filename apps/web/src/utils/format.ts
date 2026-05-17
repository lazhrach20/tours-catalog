export function formatPrice(
  amount: string | null | undefined,
  currency = "RUB",
): string {
  if (!amount) {
    return "—";
  }

  const value = Math.round(Number.parseFloat(amount));
  if (Number.isNaN(value)) {
    return "—";
  }

  const formatted = value.toLocaleString("ru-RU");
  return currency === "RUB" ? `${formatted} ₽` : `${formatted} ${currency}`;
}

export function formatDuration(days: number, nights: number | null): string {
  const nightsPart =
    nights != null && nights > 0 ? ` / ${nights} ноч.` : "";
  return `${days} дн.${nightsPart}`;
}

export function formatDateRu(isoDate: string | null | undefined): string {
  if (!isoDate) {
    return "—";
  }

  const date = new Date(`${isoDate}T00:00:00`);
  if (Number.isNaN(date.getTime())) {
    return isoDate;
  }

  return new Intl.DateTimeFormat("ru-RU", {
    day: "numeric",
    month: "long",
    year: "numeric",
  }).format(date);
}
