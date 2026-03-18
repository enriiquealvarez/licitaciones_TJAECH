import os
import urllib.request

url = "https://lh3.googleusercontent.com/aida/AOfcidXATYjUIAkfmmur4OTMigEgBSW84ILaSkyK8Kr5fPHBUMs2Vm8sy4wAooyhOVvRG6LZjfICfzWfTtejuwm2n5FBPGnXKiDMt26NUtf5huUAx8J5kNkplVeQVSH4mTj0fD42cgrvaNxwiwuu1ccbfe8dwG___Etsk9lQPgpcBjwOjWIAL3QzbnMXPQnoN0HyTynyn6D4c10F0R7o3K5AkG-0S_L4WRyaI2ZvjOyKJN6d27CVy-Q-u4EyyDhc8dNHibKULOa6ieI"
os.makedirs("public/assets", exist_ok=True)
urllib.request.urlretrieve(url, "public/assets/logo_tjaech.png")
print("Downloaded successfully.")
