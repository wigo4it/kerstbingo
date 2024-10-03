using System.Net;
using System.Text;
using System.Text.Json;
using Azure;
using Azure.Data.Tables;
using Microsoft.Azure.Functions.Worker;
using Microsoft.Azure.Functions.Worker.Http;
using Microsoft.Extensions.Configuration;
using Microsoft.Extensions.Logging;

namespace Api
{
    public class Ticket : ITableEntity
    {
        public string PartitionKey { get; set; }
        public string RowKey { get; set; }
        public bool isdrawn { get; set; }
        public DateTimeOffset? Timestamp { get; set; }
        public ETag ETag { get; set; }
    }

    public class ApiFunction
    {
        private readonly ILogger _logger;
        private readonly string _connectionString;
        private readonly string _magicword;

        public ApiFunction(ILoggerFactory loggerFactory, IConfiguration configuration)
        {
            _logger = loggerFactory.CreateLogger<ApiFunction>();
            _connectionString = configuration["connectionString"]!;
            _magicword = configuration["magicword"]!;
        }

        private TableClient GetTableClient(string table)
        {
            return new TableClient(_connectionString, table);
        }

        [Function("resetdeelnemers")]
        public async Task<HttpResponseData> ResetDeelnemers([HttpTrigger(AuthorizationLevel.Anonymous, "get")] HttpRequestData req)
        {
            var response = req.CreateResponse(HttpStatusCode.OK);

            // Controleer of de juiste magicword is meegegeven
            if (req.Query["magicword"] != _magicword)
            {
                var response = req.CreateResponse(HttpStatusCode.Unauthorized); // Gebruik een meer passende HTTP status code
                await response.WriteStringAsync("Je hebt niet het juiste magic word gezegd!");
                return response;
            }

            _logger.LogInformation("Resetting participants table.");

            var tableClient = GetTableClient("participants");

            // Stap 1: Leeg de tabel participants
            var entities = tableClient.QueryAsync<TableEntity>();
            await foreach (var entity in entities)
            {
                await tableClient.DeleteEntityAsync(entity.PartitionKey, entity.RowKey);
            }

            // Controleer of "golden" als parameter wordt meegegeven
            if (req.Query.TryGetValue("golden", out var goldenValue))
            {
                // Stap 2: Voeg de testdeelnemers coin en goldenTicket toe
                var coin = new TableEntity
                {
                    PartitionKey = "deelnemer",
                    RowKey = "Golden Ticket!"
                };

                var goldenTicket = new TableEntity
                {
                    PartitionKey = "deelnemer",
                    RowKey = "Coin"
                };

                await tableClient.AddEntityAsync(coin);
                await tableClient.AddEntityAsync(goldenTicket);

                await response.Body.WriteAsync(Encoding.UTF8.GetBytes("Coin en Golden Ticket zijn toegevoegd."));
            }
            else
            {
                await response.Body.WriteAsync(Encoding.UTF8.GetBytes("Deelnemers zijn gereset."));
            }

            return response;
        }


        [Function("resetticket")]
        public async Task<HttpResponseData> ResetTicket([HttpTrigger(AuthorizationLevel.Anonymous, "get")] HttpRequestData req)
        {
            var response = req.CreateResponse(HttpStatusCode.OK);
            if (req.Query["magicword"] != _magicword)
            {
                var response = req.CreateResponse(HttpStatusCode.Unauthorized); // Gebruik een meer passende HTTP status code
                await response.WriteStringAsync("Je hebt niet het juiste magic word gezegd!");
                return response;
            }


            _logger.LogInformation("HTTP trigger function processed a request.");

            var tableClient = GetTableClient("goldenticket");

            var partitionKey = "ticket";
            var rowKey = "drawn";

            await tableClient.UpsertEntityAsync(new Ticket
            {
                isdrawn = false,
                PartitionKey = partitionKey,
                RowKey = rowKey
            });

            await response.Body.WriteAsync(Encoding.UTF8.GetBytes("Golden ticket  is gereset"));
            return response;
        }

        [Function("draw")]
        public async Task<string> Ticket([HttpTrigger(AuthorizationLevel.Anonymous, "get")] HttpRequestData req)
        {
            _logger.LogInformation("HTTP trigger function processed a request.");

            var ticketTableClient = GetTableClient("goldenticket");

            var partitionKey = "ticket";
            var rowKey = "drawn";
            var goldenTicket = await ticketTableClient.GetEntityAsync<Ticket>(partitionKey, rowKey);

            if (goldenTicket.Value.isdrawn)
            {
                return "";
            }

            var deelnemerTableClient = GetTableClient("participants");

            var deelnemers = await GetDeelnemers(deelnemerTableClient);

            var random = new Random();
            var winner = deelnemers[random.Next(deelnemers.Count)];

            goldenTicket.Value.isdrawn = true;

            await ticketTableClient.UpdateEntityAsync(goldenTicket.Value, goldenTicket.Value.ETag);

            return winner;
        }

        [Function("deelnemers")]
        public async Task<string> Deelnemer([HttpTrigger(AuthorizationLevel.Anonymous, "get", "post")] HttpRequestData req)
        {
            try
            {
                _logger.LogInformation("HTTP trigger function processed a request.");

                var tableClient = GetTableClient("participants");

                if (req.Method == "GET")
                {
                    List<string> deelnemers = await GetDeelnemers(tableClient);

                    return JsonSerializer.Serialize(deelnemers);
                }

                if (req.Method == "POST")
                {
                    var deelnemer = new TableEntity
                    {
                        PartitionKey = "deelnemer",
                        RowKey = await new StreamReader(req.Body).ReadToEndAsync()
                    };

                    await tableClient.AddEntityAsync(deelnemer);
                    return HttpStatusCode.OK.ToString();
                }
            }
            catch (RequestFailedException ex)
            {
                if (ex.ErrorCode == "EntityAlreadyExists")
                {
                    return HttpStatusCode.Conflict.ToString();
                }
            }
            catch (Exception ex)
            {
                return HttpStatusCode.InternalServerError.ToString();
            }

            return HttpStatusCode.Forbidden.ToString();
        }

        private static async Task<List<string>> GetDeelnemers(TableClient tableClient)
        {
            var results = new List<string>();
            await foreach (var deelnemer in tableClient.QueryAsync<TableEntity>())
            {
                results.Add(deelnemer.RowKey);
            }

            return results;
        }
    }
}
