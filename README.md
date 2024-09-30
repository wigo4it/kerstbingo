# techorama2024

# Project Name

## Prerequisites

To run this project, you need to have the following tools installed:

1. **Node.js (LTS version)**
2. **Azure Functions Core Tools**
3. **Azure Static Web Apps CLI (SWA CLI)**

## Installation

1. **Install Node.js (LTS version):**
   Download and install the LTS version of Node.js from the [official website](https://nodejs.org/).

2. **Install Azure Functions Core Tools:**
   `npm install -g azure-functions-core-tools@4` 

3. **Install Azure Static Web Apps CLI (SWA CLI):**
   `npm install -g @azure/static-web-apps-cli`

## Run the App locally 
   **Add User Secrets for .NET:

   inside the API project run the following commands:

   - Initialize user secrets in your project:
      `dotnet user-secrets init`

   - Set the connection string:
      `dotnet user-secrets set "connectionString" "<YOUR_CONNECTION_STRING>"`

   - Set the magic word:
      `dotnet user-secrets set "magicword" "<YOUR_MAGIC_WORD>"`
 
 - run `swa start .\App\  api --api-location .\Api\`

## Deploying the app
 1. Create a static web app in azure 
 - Set build preset to custom 
 - set App location to /App
 - set Api location to /Api

2. After the app is deployed 

 - Set the connectionstring from the table storage as an appsetting in the webapp:
 `az staticwebapp appsettings set --name <YOUR_APP_ID> --setting-names "connectionString=<KEY>"`

 - Set the magicword to a secret word or text to access admin functions on the API
 `az staticwebapp appsettings set --name <YOUR_APP_ID> --setting-names "magicword=<MagicWord>"`


 ## App navigation

 - shift + l for the loterij
 - shift + s for namen
 - shift + g for game

 ## Admin Api endpoints

 - /api/resetdeelnemers?magicword=<MAGIC_WORD>
 - /api/resetticket?magicword=<MAGIC_WORD>

