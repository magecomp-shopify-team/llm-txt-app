import React from "react";
import {
  Page,
  Layout,
  Card,
  Text,
  DescriptionList,
  List,
  BlockStack,
  InlineStack,
  Badge
} from "@shopify/polaris";

const DashboardPage = () => {
  return (
    <Page title="Dashboard" subtitle="Business Overview">
      <Layout>

        {/* Summary section */}
        <Layout.Section>
          <Card>
            <BlockStack gap="400">
              <Text variant="headingLg" as="h2">
                Key Metrics
              </Text>
              <DescriptionList
                items={[
                  { term: "Total Sales", description: "$24,580" },
                  { term: "New Customers", description: "340" },
                  { term: "Refunds", description: "12" },
                  { term: "Conversion Rate", description: "4.8%" },
                ]}
              />
            </BlockStack>
          </Card>
        </Layout.Section>

        {/* Orders + Customers side by side */}
        <Layout.Section oneHalf>
          <Card title="Recent Orders">
            <List type="bullet">
              <List.Item>
                Order #1051 – $320 <Badge status="success">Paid</Badge>
              </List.Item>
              <List.Item>
                Order #1050 – $145 <Badge status="attention">Pending</Badge>
              </List.Item>
              <List.Item>
                Order #1049 – $210 <Badge status="warning">Refund</Badge>
              </List.Item>
            </List>
          </Card>
        </Layout.Section>

        <Layout.Section oneHalf>
          <Card title="Top Customers">
            <List>
              <List.Item>John Doe – 15 orders</List.Item>
              <List.Item>Jane Smith – 12 orders</List.Item>
              <List.Item>David Lee – 8 orders</List.Item>
            </List>
          </Card>
        </Layout.Section>

        {/* Status section */}
        <Layout.Section>
          <Card title="System Status">
            <InlineStack gap="400">
              <Badge status="success">API Connected</Badge>
              <Badge status="success">Payments Active</Badge>
              <Badge status="attention">Low Stock</Badge>
            </InlineStack>
          </Card>
        </Layout.Section>

      </Layout>
    </Page>
  );
};

export default DashboardPage;
