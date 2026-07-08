import requests

APOLLO_API_KEY = 'j94WT-5ZjKx1ytaOjXPQuQ'
HUNTER_API_KEY = 'a3712c2beb7c0e64f83cbd97a8d2c58928af4e80'

print("--- PROBANDO APOLLO ---")
res_apollo = requests.post(
    "https://api.apollo.io/v1/mixed_people/search",
    headers={"Cache-Control": "no-cache", "Content-Type": "application/json"},
    json={"api_key": APOLLO_API_KEY, "q_organization_domains": "vasalogistics.com", "per_page": 1}
)
print(f"Status Code Apollo: {res_apollo.status_code}")
try: print(res_apollo.json())
except: print(res_apollo.text)

print("\n--- PROBANDO HUNTER ---")
res_hunter = requests.get(f"https://api.hunter.io/v2/domain-search?domain=vasalogistics.com&api_key={HUNTER_API_KEY}")
print(f"Status Code Hunter: {res_hunter.status_code}")
try: print(res_hunter.json())
except: print(res_hunter.text)
