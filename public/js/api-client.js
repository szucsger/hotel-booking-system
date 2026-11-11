// A böngészőoldali műveletek közös POST-kérését és JSON-válaszát kezeli.
export async function postApiRequest(endpoint, formData) {
  let response;

  try {
    response = await fetch(endpoint, {
      method: "POST",
      headers: { Accept: "application/json" },
      body: formData,
    });
  } catch (error) {
    console.error("Reception API request failed:", error);
    throw new Error("Nem sikerült kapcsolatba lépni a szerverrel.");
  }

  const responseText = await response.text();
  let result;

  try {
    result = JSON.parse(responseText);
  } catch (error) {
    console.error("Reception API returned non-JSON:", responseText, error);
    throw new Error(
      "A szerver hibás választ adott (HTTP " + response.status + ").",
    );
  }

  if (!result || typeof result !== "object" || Array.isArray(result)) {
    throw new Error("A szerver hibás választ adott.");
  }

  if (!response.ok || result.success !== true) {
    throw new Error(result.message || "A kérés feldolgozása nem sikerült.");
  }

  return result;
}
